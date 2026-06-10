<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\ServiceOffer;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use App\Traits\ResponseFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ServiceRequestController extends Controller
{
    use ResponseFormat;

    protected WalletService $walletService;

    protected const PLATFORM_COMMISSION_PERCENT = 10;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Create a new service request (buyer)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->ResponseUnauthorize('You must be logged in');
        }

        $request->validate([
            'service_offer_id' => 'required|uuid|exists:service_offers,id',
            'description' => 'nullable|string',
            'details' => 'nullable|array',
            'scheduled_at' => 'nullable|date',
        ]);

        DB::beginTransaction();

        try {
            $serviceOffer = ServiceOffer::lockForUpdate()->find($request->service_offer_id);
            if (!$serviceOffer || !$serviceOffer->is_available || $serviceOffer->status !== 'active') {
                return $this->ResponseERROR('Service offer not available');
            }

            $totalAmount = $serviceOffer->price;

            $buyerWallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            if (!$buyerWallet || $buyerWallet->available_balance < $totalAmount) {
                return $this->ResponseERROR('Insufficient wallet balance');
            }

            $baseCurrency = Currency::where('is_base', true)->first() ?? Currency::where('code', 'XOF')->first();
            if (!$baseCurrency) {
                return $this->ResponseERROR('Currency not configured');
            }


            $serviceRequest = ServiceRequest::create([
                'buyer_id' => $user->id,
                'merchant_profile_id' => $serviceOffer->merchant_profile_id,
                'service_offer_id' => $serviceOffer->id,
                'status' => 'pending',
                'description' => $request->description,
                'details' => $request->details,
                'quoted_price' => $totalAmount,
                'final_price' => $totalAmount,
                'scheduled_at' => $request->scheduled_at,
                'currency_id' => $baseCurrency->id,
            ]);

            // Dans ServiceRequestController store()
            $provider = User::whereHas('merchantProfiles', function ($q) use ($serviceOffer) {
                $q->where('id', $serviceOffer->merchant_profile_id);
            })->first();

            $this->walletService->holdFunds(
                $buyerWallet,
                $totalAmount,
                $serviceRequest,
                $provider->id,
                "Hold for service request #{$serviceRequest->request_number}"
            );

            DB::commit();

            return $this->ResponseCreated('Service request created', [
                'service_request_id' => $serviceRequest->id,
                'request_number' => $serviceRequest->request_number,
                'total' => $totalAmount,
                'status' => 'pending'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('store error: ' . $e->getMessage());
            return $this->ResponseServerError('Creation failed', $e->getMessage());
        }
    }

    /**
     * Show a service request (buyer or provider)
     */
    public function show($id)
    {
        $user = Auth::user();
        $serviceRequest = ServiceRequest::with(['buyer', 'merchantProfile', 'serviceOffer'])
            ->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                    ->orWhereIn('merchant_profile_id', $user->merchantProfiles->pluck('id'));
            })
            ->findOrFail($id);

        return $this->ResponseOk('Service request details', ['service_request' => $serviceRequest]);
    }

    /**
     * List requests for buyer
     */
    public function index()
    {
        $user = Auth::user();
        $requests = ServiceRequest::where('buyer_id', $user->id)
            ->with(['merchantProfile', 'serviceOffer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->ResponseOk('Service requests', ['service_requests' => $requests]);
    }

    /**
     * List requests for seller (provider)
     */
    public function sellerRequests()
    {
        $user = Auth::user();
        $profileIds = $user->merchantProfiles->pluck('id');
        if ($profileIds->isEmpty()) {
            return $this->ResponseOk('No requests', ['service_requests' => []]);
        }

        $requests = ServiceRequest::whereIn('merchant_profile_id', $profileIds)
            ->with(['buyer', 'serviceOffer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->ResponseOk('Seller service requests', ['service_requests' => $requests]);
    }

    /**
     * Cancel request (buyer only)
     */
    public function cancelRequest($id, Request $request)
    {
        $user = Auth::user();
        DB::beginTransaction();

        try {
            $serviceRequest = ServiceRequest::findOrFail($id);
            if ($serviceRequest->buyer_id !== $user->id) {
                return $this->ResponseUnauthorize('Not your request');
            }
            if (!in_array($serviceRequest->status, ['pending', 'accepted'])) {
                return $this->ResponseERROR('Cannot cancel, current status: ' . $serviceRequest->status);
            }

            $hold = $this->walletService->findPendingHold($serviceRequest);
            if ($hold) {
                $this->walletService->refundFunds($hold, $request->input('reason', 'Cancelled by buyer'));
            }

            $serviceRequest->status = 'cancelled';
            $serviceRequest->cancelled_at = now();
            $serviceRequest->cancelled_by = 'buyer';
            $serviceRequest->cancellation_reason = $request->input('reason', 'Cancelled by buyer');
            $serviceRequest->save();

            DB::commit();
            return $this->ResponseOk('Request cancelled and refunded', ['refunded_amount' => $serviceRequest->final_price]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->ResponseServerError('Cancellation failed', $e->getMessage());
        }
    }

    /**
     * Accept request (provider)
     */
    public function acceptRequest($id)
    {
        $user = Auth::user();
        DB::beginTransaction();

        try {
            $serviceRequest = ServiceRequest::findOrFail($id);
            // Vérification correcte : l'utilisateur possède-t-il le profil marchand de la demande ?
            if (!$user->merchantProfiles->contains('id', $serviceRequest->merchant_profile_id)) {
                return $this->ResponseUnauthorize('You are not the provider of this service');
            }
            if ($serviceRequest->status !== 'pending') {
                return $this->ResponseERROR('Request cannot be accepted, status: ' . $serviceRequest->status);
            }

            $serviceRequest->status = 'accepted';
            $serviceRequest->accepted_at = now();
            $serviceRequest->save();

            DB::commit();
            return $this->ResponseOk('Request accepted');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->ResponseServerError('Accept failed', $e->getMessage());
        }
    }

    /**
     * Reject request (provider)
     */
    public function rejectRequest($id, Request $request)
    {
        $user = Auth::user();
        DB::beginTransaction();

        try {
            $serviceRequest = ServiceRequest::findOrFail($id);
            if (!$user->merchantProfiles->contains('id', $serviceRequest->merchant_profile_id)) {
                return $this->ResponseUnauthorize('You are not the provider');
            }
            if ($serviceRequest->status !== 'pending') {
                return $this->ResponseERROR('Cannot reject, status: ' . $serviceRequest->status);
            }

            $hold = $this->walletService->findPendingHold($serviceRequest);
            if ($hold) {
                $this->walletService->refundFunds($hold, $request->input('reason', 'Rejected by provider'));
            }

            $serviceRequest->status = 'rejected';
            $serviceRequest->rejected_at = now();
            $serviceRequest->cancellation_reason = $request->input('reason', 'Rejected by provider');
            $serviceRequest->save();

            DB::commit();
            return $this->ResponseOk('Request rejected, buyer refunded');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->ResponseServerError('Rejection failed', $e->getMessage());
        }
    }

    /**
     * Mark as started (provider)
     */
    public function markAsStarted($id)
    {
        $user = Auth::user();
        DB::beginTransaction();

        try {
            $serviceRequest = ServiceRequest::findOrFail($id);
            if (!$user->merchantProfiles->contains('id', $serviceRequest->merchant_profile_id)) {
                return $this->ResponseUnauthorize('You are not the provider');
            }
            if ($serviceRequest->status !== 'accepted') {
                return $this->ResponseERROR('Cannot start, status: ' . $serviceRequest->status);
            }

            $serviceRequest->status = 'in_progress';
            $serviceRequest->started_at = now();
            $serviceRequest->save();

            DB::commit();
            return $this->ResponseOk('Service started');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->ResponseServerError('Start failed', $e->getMessage());
        }
    }

    /**
     * Mark as completed (provider) – releases payment
     */
    public function markAsCompleted($id)
    {
        $user = Auth::user();
        DB::beginTransaction();

        try {
            $serviceRequest = ServiceRequest::findOrFail($id);
            if (!$user->merchantProfiles->contains('id', $serviceRequest->merchant_profile_id)) {
                return $this->ResponseUnauthorize('You are not the provider');
            }
            if ($serviceRequest->status !== 'in_progress') {
                return $this->ResponseERROR('Cannot complete, status: ' . $serviceRequest->status);
            }

            $hold = $this->walletService->findPendingHold($serviceRequest);
            if ($hold) {
                $this->walletService->releaseFunds($hold, self::PLATFORM_COMMISSION_PERCENT);
            }

            $serviceRequest->status = 'completed';
            $serviceRequest->completed_at = now();
            $serviceRequest->save();

            DB::commit();
            return $this->ResponseOk('Service completed, payment released');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur: ' . $e->getMessage());

            return $this->ResponseServerError('Completion failed', $e->getMessage());
        }
    }

    /**
     * Buyer confirms completion (releases payment)
     */
    public function confirmCompletion($id)
    {
        $user = Auth::user();
        DB::beginTransaction();

        try {
            $serviceRequest = ServiceRequest::findOrFail($id);
            if ($serviceRequest->buyer_id !== $user->id) {
                return $this->ResponseUnauthorize('You are not the buyer');
            }
            if ($serviceRequest->status !== 'in_progress') {
                return $this->ResponseERROR('Cannot confirm, status: ' . $serviceRequest->status);
            }

            $hold = $this->walletService->findPendingHold($serviceRequest);
            if ($hold) {
                $this->walletService->releaseFunds($hold, self::PLATFORM_COMMISSION_PERCENT);
            }

            $serviceRequest->status = 'completed';
            $serviceRequest->completed_at = now();
            $serviceRequest->save();

            DB::commit();
            return $this->ResponseOk('Completion confirmed, payment released');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur: ' . $e->getMessage());

            return $this->ResponseServerError('Confirmation failed', $e->getMessage());
        }
    }
}
