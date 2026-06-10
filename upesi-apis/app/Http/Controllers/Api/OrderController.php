<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Wallet;
use App\Services\WalletService;
use App\Traits\ResponseFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    use ResponseFormat;

    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Create a new order (multi-vendor)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->ResponseUnauthorize('You must be logged in');
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|uuid|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'shipping_address' => 'required|array',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // 1. Check each product and filter available ones
            $availableItems = [];
            $unavailableItems = [];

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if (!$product) {
                    $unavailableItems[] = [
                        'product_id' => $item['product_id'],
                        'name' => 'Unknown product',
                        'reason' => 'Product not found'
                    ];
                    continue;
                }

                if ($product->quantity < $item['quantity']) {
                    $unavailableItems[] = [
                        'product_id' => $product->id,
                        'name' => $product->title,
                        'requested' => $item['quantity'],
                        'available' => $product->quantity,
                        'reason' => 'Insufficient stock'
                    ];
                    continue;
                }

                $availableItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $product->unit_price
                ];
            }

            // 2. If no items available
            if (empty($availableItems)) {
                DB::rollBack();
                return $this->ResponseERROR('No products available in your cart', [
                    'unavailable_items' => $unavailableItems
                ]);
            }

            // 3. Group available items by seller
            $itemsBySeller = [];
            $totalAmount = 0;

            foreach ($availableItems as $item) {
                $sellerId = $item['product']->merchant_profile_id;

                if (!isset($itemsBySeller[$sellerId])) {
                    $itemsBySeller[$sellerId] = [];
                }

                $itemsBySeller[$sellerId][] = $item;
                $totalAmount += $item['price'] * $item['quantity'];
            }

            // 4. Check buyer wallet
            $buyerWallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

            if (!$buyerWallet || $buyerWallet->available_balance < $totalAmount) {
                DB::rollBack();
                return $this->ResponseERROR('Insufficient wallet balance', [
                    'required' => $totalAmount,
                    'available' => $buyerWallet->available_balance ?? 0
                ]);
            }

            // 5. Get base currency (XOF)
            $baseCurrency = Currency::where('is_base', true)->first();
            if (!$baseCurrency) {
                $baseCurrency = Currency::where('code', 'XOF')->first();
            }

            if (!$baseCurrency) {
                DB::rollBack();
                return $this->ResponseERROR('Currency not configured. Please contact support.');
            }

            // 6. Generate order number

            // 7. Create parent order
            $order = Order::create([
                'buyer_id' => $user->id,
                'currency_id' => $baseCurrency->id,  // ✅ AJOUTÉ
                'status' => 'pending',
                'payment_status' => 'pending',
                'subtotal' => $totalAmount,
                'total' => $totalAmount,
                'shipping_address' => $request->shipping_address,
                'notes' => $request->notes ?? '',
                'ordered_at' => now(),
                'metadata' => !empty($unavailableItems) ? ['unavailable_items' => $unavailableItems] : null
            ]);

            // 8. Create order items and hold funds
            foreach ($itemsBySeller as $sellerId => $items) {
                $sellerTotal = 0;

                foreach ($items as $item) {
                    $subtotal = $item['price'] * $item['quantity'];
                    $sellerTotal += $subtotal;

                    // Create order item
                    OrderItem::create([
                        'order_id' => $order->id,
                        'merchant_profile_id' => $sellerId,
                        'product_id' => $item['product']->id,
                        'product_name' => $item['product']->title,
                        'unit_id' => $item['product']->unit_id,  // ✅ AJOUTE CETTE LIGNE
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'subtotal' => $subtotal,
                        'total' => $subtotal,
                        'seller_status' => 'pending'
                    ]);

                    // Deduct stock
                    $item['product']->decrement('quantity', $item['quantity']);
                }

                // Hold funds for this seller
                $this->walletService->holdFunds(
                    $buyerWallet,
                    $sellerTotal,
                    $order,
                    $sellerId,
                    "Hold for order #{$order->order_number}"
                );
            }

            DB::commit();

            $message = 'Order created successfully';
            if (!empty($unavailableItems)) {
                $message = count($unavailableItems) . ' product(s) were unavailable and removed from your order. ' . count($availableItems) . ' product(s) ordered successfully.';
            }

            return $this->ResponseCreated($message, [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $totalAmount,
                'items_count' => count($availableItems),
                'unavailable_items' => $unavailableItems
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating order: ' . $e->getMessage());
            return $this->ResponseServerError('Order creation failed', $e->getMessage());
        }
    }

    /**
     * Get order details
     */
    public function show($orderId)
    {
        $user = Auth::user();

        $order = Order::with(['items.merchantProfile', 'items.product'])
            ->where('buyer_id', $user->id)
            ->findOrFail($orderId);

        return $this->ResponseOk('Order details', ['order' => $order]);
    }

    /**
     * Get all orders for the authenticated user
     */
    public function index()
    {
        $user = Auth::user();

        $orders = Order::where('buyer_id', $user->id)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->ResponseOk('Orders list', ['orders' => $orders]);
    }

    /**
     * Cancel entire order (buyer only)
     */
    public function cancelWholeOrder($orderId, Request $request)
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            $order = Order::with('items')->findOrFail($orderId);

            if ($order->buyer_id !== $user->id) {
                return $this->ResponseUnauthorize('You are not authorized to cancel this order');
            }

            if (!in_array($order->status, ['pending'])) {
                return $this->ResponseERROR('This order cannot be cancelled. Current status: ' . $order->status);
            }

            $cancelledItems = 0;
            $refundedAmount = 0;

            foreach ($order->items as $item) {
                if (in_array($item->seller_status, ['pending', 'confirmed'])) {
                    // Refund this item
                    $hold = $this->walletService->findPendingHold($order, $item->merchant_profile_id);
                    if ($hold) {
                        $this->walletService->refundFunds($hold, $request->input('reason', 'Order cancelled by buyer'));
                        $refundedAmount += $item->subtotal;
                    }

                    // Restore stock
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('quantity', $item->quantity);
                    }

                    $item->seller_status = 'cancelled';
                    $item->cancelled_by = 'buyer';
                    $item->cancellation_reason = $request->input('reason', 'Order cancelled');
                    $item->save();

                    $cancelledItems++;
                }
            }

            $order->status = 'cancelled';
            $order->payment_status = 'refunded';
            $order->cancelled_at = now();
            $order->cancelled_by = $user->id;
            $order->cancellation_reason = $request->input('reason', 'Order cancelled');
            $order->save();

            DB::commit();

            return $this->ResponseOk('Order cancelled and refunded successfully', [
                'order_id' => $order->id,
                'cancelled_items' => $cancelledItems,
                'refunded_amount' => $refundedAmount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->ResponseServerError('Cancellation failed', $e->getMessage());
        }
    }

    /**
     * Cancel single item (buyer or seller)
     */
    public function cancelItem($itemId, Request $request)
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            $item = OrderItem::with('order')->findOrFail($itemId);

            $isBuyer = ($item->order->buyer_id === $user->id);
            $isSeller = ($item->merchant_profile_id === $user->merchantProfiles->first()?->id);

            if (!$isBuyer && !$isSeller) {
                return $this->ResponseUnauthorize('You are not authorized to cancel this item');
            }

            if (!in_array($item->seller_status, ['pending', 'confirmed'])) {
                return $this->ResponseERROR('This item cannot be cancelled. Current status: ' . $item->seller_status);
            }

            // Refund the item
            $hold = $this->walletService->findPendingHold($item->order, $item->merchant_profile_id);
            if ($hold) {
                $refundAmount = $item->subtotal;
                $this->walletService->refundFunds($hold, $request->input('reason', 'Item cancelled by ' . ($isBuyer ? 'buyer' : 'seller')));
            }

            // Restore stock
            $product = Product::find($item->product_id);
            if ($product) {
                $product->increment('quantity', $item->quantity);
            }

            // Update item status
            $item->seller_status = 'cancelled';
            $item->cancelled_by = $isBuyer ? 'buyer' : 'seller';
            $item->cancellation_reason = $request->input('reason', 'Item cancelled');
            $item->save();

            // Check if all items in order are cancelled
            $remainingItems = OrderItem::where('order_id', $item->order_id)
                ->whereNotIn('seller_status', ['cancelled', 'refunded'])
                ->count();

            $order = $item->order;

            if ($remainingItems === 0) {
                $order->status = 'cancelled';
                $order->payment_status = 'refunded';
                $order->cancelled_at = now();
            } else {
                $order->status = 'partial_cancelled';
                $order->payment_status = 'partial_refund';
            }
            $order->save();

            DB::commit();

            return $this->ResponseOk('Item cancelled successfully', [
                'item_id' => $item->id,
                'order_id' => $item->order_id,
                'refunded_amount' => $item->subtotal
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->ResponseServerError('Cancellation failed', $e->getMessage());
        }
    }

    /**
     * Cancel all items from a specific seller (seller only)
     */
    public function cancelSellerItems($orderId, Request $request)
    {
        $user = Auth::user();

        $merchantProfileId = $user->merchantProfiles->first()?->id;

        if (!$merchantProfileId) {
            return $this->ResponseERROR('No merchant profile found');
        }

        DB::beginTransaction();

        try {
            $items = OrderItem::where('order_id', $orderId)
                ->where('merchant_profile_id', $merchantProfileId)
                ->whereIn('seller_status', ['pending', 'confirmed'])
                ->get();

            if ($items->isEmpty()) {
                return $this->ResponseERROR('No cancellable items found');
            }

            $cancelledCount = 0;
            $refundedAmount = 0;

            foreach ($items as $item) {
                // Refund this item
                $hold = $this->walletService->findPendingHold($item->order, $merchantProfileId);
                if ($hold) {
                    $this->walletService->refundFunds($hold, $request->input('reason', 'Seller cancelled - product unavailable'));
                    $refundedAmount += $item->subtotal;
                }

                // Restore stock
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('quantity', $item->quantity);
                }

                $item->seller_status = 'cancelled';
                $item->cancelled_by = 'seller';
                $item->cancellation_reason = $request->input('reason', 'Product unavailable');
                $item->save();

                $cancelledCount++;
            }

            // Check if all items in order are cancelled
            $remainingItems = OrderItem::where('order_id', $orderId)
                ->whereNotIn('seller_status', ['cancelled', 'refunded'])
                ->count();

            $order = Order::find($orderId);

            if ($remainingItems === 0) {
                $order->status = 'cancelled';
                $order->payment_status = 'refunded';
                $order->cancelled_at = now();
            } else {
                $order->status = 'partial_cancelled';
                $order->payment_status = 'partial_refund';
            }
            $order->save();

            DB::commit();

            return $this->ResponseOk('Seller items cancelled successfully', [
                'order_id' => $orderId,
                'cancelled_items' => $cancelledCount,
                'refunded_amount' => $refundedAmount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->ResponseServerError('Cancellation failed', $e->getMessage());
        }
    }

    /**
     * Confirm an item (seller confirms they will deliver)
     */
    public function confirmItem($itemId)
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            $item = OrderItem::with('order')->findOrFail($itemId);

            $merchantProfileId = $user->merchantProfiles->first()?->id;

            if ($item->merchant_profile_id !== $merchantProfileId) {
                return $this->ResponseUnauthorize('You are not the seller of this product');
            }

            if ($item->seller_status !== 'pending') {
                return $this->ResponseERROR('Item cannot be confirmed. Current status: ' . $item->seller_status);
            }

            $item->seller_status = 'confirmed';
            $item->seller_confirmed_at = now();
            $item->save();

            DB::commit();

            return $this->ResponseOk('Item confirmed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->ResponseServerError('Confirmation failed', $e->getMessage());
        }
    }

    /**
     * Mark item as shipped (seller)
     */
    public function markAsShipped($itemId, Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'tracking_number' => 'nullable|string',
            'shipping_carrier' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $item = OrderItem::with('order')->findOrFail($itemId);

            $merchantProfileId = $user->merchantProfiles->first()?->id;

            if ($item->merchant_profile_id !== $merchantProfileId) {
                return $this->ResponseUnauthorize('You are not the seller of this product');
            }

            if ($item->seller_status !== 'confirmed') {
                return $this->ResponseERROR('Item cannot be marked as shipped. Current status: ' . $item->seller_status);
            }

            $item->seller_status = 'shipped';
            $item->seller_shipped_at = now();
            $item->tracking_number = $request->tracking_number;
            $item->shipping_carrier = $request->shipping_carrier;
            $item->save();

            DB::commit();

            return $this->ResponseOk('Item marked as shipped');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->ResponseServerError('Update failed', $e->getMessage());
        }
    }

    /**
     * Confirm delivery and release payment to seller (buyer)
     */
    public function confirmDelivery($itemId)
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            $item = OrderItem::with('order')->findOrFail($itemId);

            if ($item->order->buyer_id !== $user->id) {
                return $this->ResponseUnauthorize('You are not the buyer');
            }

            if ($item->seller_status !== 'shipped') {
                return $this->ResponseERROR('Item cannot be confirmed as delivered. Current status: ' . $item->seller_status);
            }

            // Release funds to seller
            $hold = $this->walletService->findPendingHold($item->order, $item->merchant_profile_id);
            if ($hold) {
                $commissionRate = $this->getCommissionRate($item->merchant_profile_id);
                $this->walletService->releaseFunds($hold, $commissionRate);
            }

            $item->seller_status = 'delivered';
            $item->seller_delivered_at = now();
            $item->seller_paid_at = now();
            $item->save();

            // Check if all items are delivered
            $allDelivered = OrderItem::where('order_id', $item->order_id)
                ->whereNotIn('seller_status', ['delivered', 'cancelled', 'refunded'])
                ->count() === 0;

            $order = $item->order;

            if ($allDelivered) {
                $order->status = 'completed';
                $order->payment_status = 'paid';
                $order->completed_at = now();
                $order->save();
            }

            DB::commit();

            return $this->ResponseOk('Delivery confirmed. Payment released to seller.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->ResponseServerError('Confirmation failed', $e->getMessage());
        }
    }

    /**
     * Get seller orders (for seller dashboard)
     */
    public function sellerOrders()
    {
        $user = Auth::user();

        $merchantProfileIds = $user->merchantProfiles->pluck('id');

        if ($merchantProfileIds->isEmpty()) {
            return $this->ResponseOk('No orders', ['orders' => []]);
        }

        $items = OrderItem::whereIn('merchant_profile_id', $merchantProfileIds)
            ->with(['order.buyer', 'order.shipping_address'])
            ->orderBy('created_at', 'desc')
            ->get();

        $grouped = $items->groupBy('order_id')->map(function ($orderItems, $orderId) {
            $firstItem = $orderItems->first();
            $order = $firstItem->order;

            return [
                'order_id' => $orderId,
                'order_number' => $order->order_number,
                'buyer_name' => $order->buyer->name,
                'ordered_at' => $order->ordered_at,
                'shipping_address' => $order->shipping_address,
                'items' => $orderItems->map(function ($item) {
                    return [
                        'item_id' => $item->id,
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'subtotal' => $item->subtotal,
                        'seller_gets' => $item->subtotal - ($item->subtotal * $this->getCommissionRate($item->merchant_profile_id) / 100),
                        'status' => $item->seller_status,
                        'tracking_number' => $item->tracking_number,
                        'shipping_carrier' => $item->shipping_carrier,
                        'confirmed_at' => $item->seller_confirmed_at,
                        'shipped_at' => $item->seller_shipped_at,
                        'delivered_at' => $item->seller_delivered_at,
                    ];
                }),
                'total_for_seller' => $orderItems->sum(function ($item) {
                    return $item->subtotal - ($item->subtotal * $this->getCommissionRate($item->merchant_profile_id) / 100);
                })
            ];
        });

        return $this->ResponseOk('Seller orders', ['orders' => $grouped->values()]);
    }



    /**
     * Get commission rate based on merchant profile type
     */
    private function getCommissionRate($merchantProfileId): float
    {
        $profile = \App\Models\MerchantProfile::find($merchantProfileId);

        if (!$profile) {
            return 5.0;
        }

        return match ($profile->type) {
            'producer' => 3.0,
            'supplier' => 5.0,
            'transporter' => 2.0,
            default => 5.0,
        };
    }
}
