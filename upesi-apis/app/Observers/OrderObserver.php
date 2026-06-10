<?php
// app/Observers/OrderObserver.php

namespace App\Observers;

use App\Mail\NewOrderVendorMail;
use App\Mail\OrderConfirmedBuyerMail;
use App\Mail\OrderStatusUpdateMail;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Quand une commande est créée
     */
    public function created(Order $order): void
    {
        Log::info('📦 OrderObserver::created déclenché', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'total' => $order->total
        ]);

        // Récupérer les vendeurs uniques
        $vendorIds = $order->items->pluck('merchant_profile_id')->unique();

        Log::info('Vendeurs trouvés pour la commande', [
            'order_id' => $order->id,
            'vendor_ids' => $vendorIds->toArray()
        ]);

        foreach ($vendorIds as $vendorId) {
            Log::info('Traitement du vendeur', [
                'order_id' => $order->id,
                'vendor_id' => $vendorId
            ]);

            $vendor = User::whereHas('merchantProfiles', function($q) use ($vendorId) {
                $q->where('id', $vendorId);
            })->first();

            if ($vendor) {
                Log::info('Vendeur trouvé, envoi des notifications', [
                    'order_id' => $order->id,
                    'vendor_id' => $vendor->id,
                    'vendor_email' => $vendor->email
                ]);

                try {
                    // Email au vendeur
                    Mail::to($vendor->email, $vendor->name)->send(new NewOrderVendorMail($order, $vendor, $vendorId));
                    Log::info('Email envoyé au vendeur', [
                        'order_id' => $order->id,
                        'vendor_email' => $vendor->email
                    ]);
                } catch (\Exception $e) {
                    Log::error('Erreur envoi email au vendeur', [
                        'order_id' => $order->id,
                        'vendor_email' => $vendor->email,
                        'error' => $e->getMessage()
                    ]);
                }

                try {
                    // Notification Filament au vendeur
                    Notification::make()
                        ->title('🛒 Nouvelle commande reçue !')
                        ->body("Commande #{$order->order_number} - " . number_format($this->getVendorTotal($order, $vendorId), 0, ',', ' ') . " CFA")
                        ->icon('heroicon-o-shopping-bag')
                        ->actions([
                            Action::make('view')
                                ->label('Voir la commande')
                                ->url("/admin/orders/{$order->id}")
                        ])
                        ->sendToDatabase($vendor);
                    Log::info('Notification Filament envoyée au vendeur', [
                        'order_id' => $order->id,
                        'vendor_id' => $vendor->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Erreur notification Filament au vendeur', [
                        'order_id' => $order->id,
                        'vendor_id' => $vendor->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                Log::warning('Vendeur non trouvé pour merchant_profile_id', [
                    'order_id' => $order->id,
                    'merchant_profile_id' => $vendorId
                ]);
            }
        }

        // Notifier l'acheteur
        $this->notifyBuyer($order);

        Log::info('OrderObserver::created terminé', ['order_id' => $order->id]);
    }

    /**
     * Quand le statut d'une commande change
     */
    public function updated(Order $order): void
    {
        Log::info('📝 OrderObserver::updated déclenché', [
            'order_id' => $order->id,
            'old_status' => $order->getOriginal('status'),
            'new_status' => $order->status
        ]);

        // Si le statut change vers 'confirmed'
        if ($order->wasChanged('status') && $order->status === 'confirmed') {
            Log::info('Statut confirmé, envoi notification', ['order_id' => $order->id]);
            $this->notifyBuyerOrderConfirmed($order);
        }

        // Si le statut change vers 'shipped'
        if ($order->wasChanged('status') && $order->status === 'shipped') {
            Log::info('Statut expédié, envoi notification', ['order_id' => $order->id]);
            $this->notifyBuyerOrderShipped($order);
        }

        // Si le statut change vers 'delivered'
        if ($order->wasChanged('status') && $order->status === 'delivered') {
            Log::info('Statut livré, envoi notification', ['order_id' => $order->id]);
            $this->notifyBuyerOrderDelivered($order);
        }
    }

    /**
     * Notifier l'acheteur de la création de commande
     */
    protected function notifyBuyer(Order $order): void
    {
        $buyer = $order->buyer;

        Log::info('Notification acheteur - création commande', [
            'order_id' => $order->id,
            'buyer_id' => $buyer->id,
            'buyer_email' => $buyer->email
        ]);

        try {
            // Email à l'acheteur
            Mail::to($buyer->email, $buyer->name)->send(new OrderConfirmedBuyerMail($order, 'created'));
            Log::info('Email création envoyé à l\'acheteur', ['order_id' => $order->id]);
        } catch (\Exception $e) {
            Log::error('Erreur email création acheteur', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }

        try {
            // Notification Filament à l'acheteur (s'il a accès)
            Notification::make()
                ->title('✅ Commande confirmée')
                ->body("Votre commande #{$order->order_number} a été confirmée et est en cours de traitement.")
                ->icon('heroicon-o-check-circle')
                ->success()
                ->sendToDatabase($buyer);
            Log::info('Notification Filament création envoyée à l\'acheteur', ['order_id' => $order->id]);
        } catch (\Exception $e) {
            Log::error('Erreur notification Filament création acheteur', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Calculer le total pour un vendeur spécifique
     */
    protected function getVendorTotal(Order $order, $vendorId): float
    {
        $total = $order->items
            ->where('merchant_profile_id', $vendorId)
            ->sum('subtotal');

        Log::debug('Calcul total vendeur', [
            'order_id' => $order->id,
            'vendor_id' => $vendorId,
            'total' => $total
        ]);

        return $total;
    }

    /**
     * Notifier l'acheteur que la commande est confirmée
     */
    protected function notifyBuyerOrderConfirmed(Order $order): void
    {
        $buyer = $order->buyer;

        Log::info('Notification acheteur - commande confirmée', [
            'order_id' => $order->id,
            'buyer_email' => $buyer->email
        ]);

        try {
            Mail::to($buyer->email, $buyer->name)->send(new OrderConfirmedBuyerMail($order, 'confirmed'));
            Log::info('Email confirmation envoyé à l\'acheteur', ['order_id' => $order->id]);
        } catch (\Exception $e) {
            Log::error('Erreur email confirmation acheteur', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }

        try {
            Notification::make()
                ->title('📦 Commande confirmée')
                ->body("Bon à savoir : votre commande #{$order->order_number} a été prise en charge par le vendeur.")
                ->info()
                ->sendToDatabase($buyer);
            Log::info('Notification Filament confirmation envoyée', ['order_id' => $order->id]);
        } catch (\Exception $e) {
            Log::error('Erreur notification Filament confirmation', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notifier l'acheteur que la commande est expédiée
     */
    protected function notifyBuyerOrderShipped(Order $order): void
    {
        $buyer = $order->buyer;

        Log::info('Notification acheteur - commande expédiée', [
            'order_id' => $order->id,
            'buyer_email' => $buyer->email
        ]);

        try {
            Mail::to($buyer->email, $buyer->name)->send(new OrderStatusUpdateMail($order, 'shipped'));
            Log::info('Email expédition envoyé à l\'acheteur', ['order_id' => $order->id]);
        } catch (\Exception $e) {
            Log::error('Erreur email expédition acheteur', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }

        try {
            Notification::make()
                ->title('🚚 Commande expédiée')
                ->body("Votre commande #{$order->order_number} est en route !")
                ->icon('heroicon-o-truck')
                ->warning()
                ->sendToDatabase($buyer);
            Log::info('Notification Filament expédition envoyée', ['order_id' => $order->id]);
        } catch (\Exception $e) {
            Log::error('Erreur notification Filament expédition', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notifier l'acheteur que la commande est livrée
     */
    protected function notifyBuyerOrderDelivered(Order $order): void
    {
        $buyer = $order->buyer;

        Log::info('Notification acheteur - commande livrée', [
            'order_id' => $order->id,
            'buyer_email' => $buyer->email
        ]);

        try {
            Mail::to($buyer->email, $buyer->name)->send(new OrderStatusUpdateMail($order, 'delivered'));
            Log::info('Email livraison envoyé à l\'acheteur', ['order_id' => $order->id]);
        } catch (\Exception $e) {
            Log::error('Erreur email livraison acheteur', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }

        try {
            Notification::make()
                ->title('🎉 Commande livrée !')
                ->body("Votre commande #{$order->order_number} a été livrée. Merci pour votre confiance !")
                ->icon('heroicon-o-home')
                ->success()
                ->sendToDatabase($buyer);
            Log::info('Notification Filament livraison envoyée', ['order_id' => $order->id]);
        } catch (\Exception $e) {
            Log::error('Erreur notification Filament livraison', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
