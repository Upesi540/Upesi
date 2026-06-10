<?php
// app/Observers/ServiceRequestObserver.php

namespace App\Observers;

use App\Mail\NewServiceRequestProviderMail;
use App\Mail\ServiceRequestAcceptedMail;
use App\Mail\ServiceRequestStatusUpdateMail;
use App\Models\ServiceRequest;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ServiceRequestObserver
{
    /**
     * Quand une demande de service est créée
     */
    public function created(ServiceRequest $serviceRequest): void
    {
        Log::info('🔔 ServiceRequestObserver::created déclenché', [
            'service_request_id' => $serviceRequest->id,
            'request_number' => $serviceRequest->request_number,
            'buyer_id' => $serviceRequest->buyer_id,
            'merchant_profile_id' => $serviceRequest->merchant_profile_id,
            'service_offer_id' => $serviceRequest->service_offer_id
        ]);

        // Récupérer le prestataire (vendeur de service)
        $provider = User::whereHas('merchantProfiles', function($q) use ($serviceRequest) {
            $q->where('id', $serviceRequest->merchant_profile_id);
        })->first();

        $buyer = $serviceRequest->buyer;

        Log::info('Prestataire récupéré', [
            'service_request_id' => $serviceRequest->id,
            'provider_found' => $provider ? 'oui' : 'non',
            'provider_id' => $provider?->id,
            'provider_email' => $provider?->email,
            'provider_name' => $provider?->name
        ]);

        if ($provider) {
            try {
                // Email au prestataire
                Mail::to($provider->email, $provider->name)->send(new NewServiceRequestProviderMail($serviceRequest, $provider));
                Log::info('Email prestataire envoyé', [
                    'service_request_id' => $serviceRequest->id,
                    'provider_email' => $provider->email
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur envoi email prestataire', [
                    'service_request_id' => $serviceRequest->id,
                    'error' => $e->getMessage()
                ]);
            }

            try {
                // Notification Filament au prestataire
                Notification::make()
                    ->title('🔔 Nouvelle demande de service')
                    ->body("{$buyer->first_name} {$buyer->last_name} a demandé votre service : {$serviceRequest->serviceOffer->title}")
                    ->icon('heroicon-o-briefcase')
                    ->actions([
                        Action::make('view')
                            ->label('Voir la demande')
                            ->url("/admin/service-requests/{$serviceRequest->id}")
                    ])
                    ->sendToDatabase($provider);
                Log::info('Notification Filament prestataire envoyée', [
                    'service_request_id' => $serviceRequest->id,
                    'provider_id' => $provider->id
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur notification Filament prestataire', [
                    'service_request_id' => $serviceRequest->id,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            Log::warning('⚠️ Prestataire non trouvé pour la demande', [
                'service_request_id' => $serviceRequest->id,
                'merchant_profile_id' => $serviceRequest->merchant_profile_id
            ]);
        }

        // Notifier l'acheteur (confirmation de création)
        try {
            Mail::to($buyer->email, $buyer->name)->send(new ServiceRequestStatusUpdateMail($serviceRequest, 'created'));
            Log::info('Email acheteur (création) envoyé', [
                'service_request_id' => $serviceRequest->id,
                'buyer_email' => $buyer->email
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email acheteur création', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);
        }

        try {
            Notification::make()
                ->title('✅ Demande de service envoyée')
                ->body("Votre demande pour {$serviceRequest->serviceOffer->title} a été envoyée au prestataire.")
                ->success()
                ->sendToDatabase($buyer);
            Log::info('Notification Filament acheteur (création) envoyée', [
                'service_request_id' => $serviceRequest->id,
                'buyer_id' => $buyer->id
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur notification Filament acheteur création', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);
        }

        Log::info('ServiceRequestObserver::created terminé', ['service_request_id' => $serviceRequest->id]);
    }

    /**
     * Quand le statut change
     */
    public function updated(ServiceRequest $serviceRequest): void
    {
        $oldStatus = $serviceRequest->getOriginal('status');
        $newStatus = $serviceRequest->status;

        Log::info('📝 ServiceRequestObserver::updated déclenché', [
            'service_request_id' => $serviceRequest->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);

        $buyer = $serviceRequest->buyer;
        $provider = User::whereHas('merchantProfiles', function($q) use ($serviceRequest) {
            $q->where('id', $serviceRequest->merchant_profile_id);
        })->first();

        // Si la demande est acceptée
        if ($serviceRequest->wasChanged('status') && $serviceRequest->status === 'accepted') {
            Log::info('✅ Demande acceptée, envoi notifications', ['service_request_id' => $serviceRequest->id]);
            $this->notifyRequestAccepted($serviceRequest, $buyer, $provider);
        }

        // Si la demande est en cours
        if ($serviceRequest->wasChanged('status') && $serviceRequest->status === 'in_progress') {
            Log::info('🚀 Service en cours, envoi notifications', ['service_request_id' => $serviceRequest->id]);
            $this->notifyRequestInProgress($serviceRequest, $buyer);
        }

        // Si la demande est terminée
        if ($serviceRequest->wasChanged('status') && $serviceRequest->status === 'completed') {
            Log::info('🎊 Service terminé, envoi notifications', ['service_request_id' => $serviceRequest->id]);
            $this->notifyRequestCompleted($serviceRequest, $buyer, $provider);
        }

        // Si la demande est rejetée
        if ($serviceRequest->wasChanged('status') && $serviceRequest->status === 'rejected') {
            Log::info('❌ Demande rejetée, envoi notifications', ['service_request_id' => $serviceRequest->id]);
            $this->notifyRequestRejected($serviceRequest, $buyer);
        }

        // Si la demande est annulée
        if ($serviceRequest->wasChanged('status') && $serviceRequest->status === 'cancelled') {
            Log::info('⚠️ Demande annulée, envoi notifications', ['service_request_id' => $serviceRequest->id]);
            $this->notifyRequestCancelled($serviceRequest, $provider);
        }
    }

    /**
     * Notification quand la demande est acceptée
     */
    protected function notifyRequestAccepted(ServiceRequest $serviceRequest, $buyer, $provider): void
    {
        Log::info('📧 notifyRequestAccepted', ['service_request_id' => $serviceRequest->id]);

        try {
            // Email à l'acheteur
            Mail::to($buyer->email, $buyer->name)->send(new ServiceRequestAcceptedMail($serviceRequest, 'accepted'));
            Log::info('Email acceptation envoyé à l\'acheteur', [
                'service_request_id' => $serviceRequest->id,
                'buyer_email' => $buyer->email
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur email acceptation acheteur', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);
        }

        try {
            // Notification Filament à l'acheteur
            Notification::make()
                ->title('🎉 Demande acceptée !')
                ->body("Le prestataire a accepté votre demande pour {$serviceRequest->serviceOffer->title}")
                ->success()
                ->sendToDatabase($buyer);
            Log::info('Notification Filament acceptation envoyée à l\'acheteur', [
                'service_request_id' => $serviceRequest->id,
                'buyer_id' => $buyer->id
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur notification Filament acceptation acheteur', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);
        }

        // Notification Filament au prestataire (confirmation)
        if ($provider) {
            try {
                Notification::make()
                    ->title('✅ Demande acceptée')
                    ->body("Vous avez accepté la demande de {$buyer->first_name} {$buyer->last_name}")
                    ->success()
                    ->sendToDatabase($provider);
                Log::info('Notification Filament acceptation envoyée au prestataire', [
                    'service_request_id' => $serviceRequest->id,
                    'provider_id' => $provider->id
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur notification Filament acceptation prestataire', [
                    'service_request_id' => $serviceRequest->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Notification quand le service est en cours
     */
    protected function notifyRequestInProgress(ServiceRequest $serviceRequest, $buyer): void
    {
        Log::info('📧 notifyRequestInProgress', ['service_request_id' => $serviceRequest->id]);

        try {
            Mail::to($buyer->email, $buyer->name)->send(new ServiceRequestStatusUpdateMail($serviceRequest, 'in_progress'));
            Log::info('Email "en cours" envoyé à l\'acheteur', ['service_request_id' => $serviceRequest->id]);
        } catch (\Exception $e) {
            Log::error('Erreur email en cours acheteur', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);
        }

        try {
            Notification::make()
                ->title('🚀 Service en cours')
                ->body("Le prestataire a commencé à travailler sur votre demande : {$serviceRequest->serviceOffer->title}")
                ->info()
                ->sendToDatabase($buyer);
            Log::info('Notification Filament "en cours" envoyée', ['service_request_id' => $serviceRequest->id]);
        } catch (\Exception $e) {
            Log::error('Erreur notification Filament en cours', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notification quand le service est terminé
     */
    protected function notifyRequestCompleted(ServiceRequest $serviceRequest, $buyer, $provider): void
    {
        Log::info('📧 notifyRequestCompleted', ['service_request_id' => $serviceRequest->id]);

        try {
            // Email à l'acheteur
            Mail::to($buyer->email, $buyer->name)->send(new ServiceRequestStatusUpdateMail($serviceRequest, 'completed'));
            Log::info('Email "terminé" envoyé à l\'acheteur', ['service_request_id' => $serviceRequest->id]);
        } catch (\Exception $e) {
            Log::error('Erreur email terminé acheteur', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);
        }

        try {
            // Notification Filament à l'acheteur
            Notification::make()
                ->title('🎊 Service terminé !')
                ->body("Le service {$serviceRequest->serviceOffer->title} est terminé. Merci pour votre confiance !")
                ->success()
                ->sendToDatabase($buyer);
            Log::info('Notification Filament "terminé" envoyée à l\'acheteur', ['service_request_id' => $serviceRequest->id]);
        } catch (\Exception $e) {
            Log::error('Erreur notification Filament terminé acheteur', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);
        }

        // Notification au prestataire
        if ($provider) {
            try {
                Notification::make()
                    ->title('💰 Paiement libéré')
                    ->body("Le paiement pour {$serviceRequest->serviceOffer->title} a été libéré sur votre compte.")
                    ->success()
                    ->sendToDatabase($provider);
                Log::info('Notification Filament paiement libéré envoyée au prestataire', [
                    'service_request_id' => $serviceRequest->id,
                    'provider_id' => $provider->id
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur notification Filament paiement prestataire', [
                    'service_request_id' => $serviceRequest->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Notification quand la demande est rejetée
     */
    protected function notifyRequestRejected(ServiceRequest $serviceRequest, $buyer): void
    {
        Log::info('📧 notifyRequestRejected', ['service_request_id' => $serviceRequest->id]);

        try {
            Mail::to($buyer->email, $buyer->name)->send(new ServiceRequestStatusUpdateMail($serviceRequest, 'rejected'));
            Log::info('Email "rejeté" envoyé à l\'acheteur', ['service_request_id' => $serviceRequest->id]);
        } catch (\Exception $e) {
            Log::error('Erreur email rejeté acheteur', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);
        }

        try {
            Notification::make()
                ->title('❌ Demande refusée')
                ->body("Le prestataire a refusé votre demande pour {$serviceRequest->serviceOffer->title}. Vous avez été remboursé.")
                ->danger()
                ->sendToDatabase($buyer);
            Log::info('Notification Filament "rejeté" envoyée', ['service_request_id' => $serviceRequest->id]);
        } catch (\Exception $e) {
            Log::error('Erreur notification Filament rejeté', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notification quand la demande est annulée par l'acheteur
     */
    protected function notifyRequestCancelled(ServiceRequest $serviceRequest, $provider): void
    {
        Log::info('📧 notifyRequestCancelled', ['service_request_id' => $serviceRequest->id]);

        if ($provider) {
            try {
                Notification::make()
                    ->title('⚠️ Demande annulée')
                    ->body("L'acheteur a annulé sa demande pour {$serviceRequest->serviceOffer->title}")
                    ->warning()
                    ->sendToDatabase($provider);
                Log::info('Notification Filament "annulée" envoyée au prestataire', [
                    'service_request_id' => $serviceRequest->id,
                    'provider_id' => $provider->id
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur notification Filament annulation prestataire', [
                    'service_request_id' => $serviceRequest->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
