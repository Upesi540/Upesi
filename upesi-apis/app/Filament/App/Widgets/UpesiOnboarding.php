<?php

namespace App\Filament\App\Widgets;

use App\Models\KycVerification;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Facades\Auth;

class UpesiOnboarding extends BaseWidget
{
    // On désactive le polling automatique (rafraîchissement 5s) pour économiser les ressources
    protected ?string $pollingInterval = null;
    protected function getStats(): array
    {
        $user = Auth::user();
        $kyc = $user->kycVerification;
        $profileCount = $user?->merchantProfiles->count();

        return [
            // STAT 1 : État du KYC (Identité)
            Stat::make('Vérification KYC', $this->getKycLabel($kyc))
                ->description($this->getKycDescription($kyc))
                ->descriptionIcon($this->getKycIcon($kyc), IconPosition::Before)
                ->color($this->getKycColor($kyc))
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onClick' => "window.location.href='/app/submit-kyc'",
                ]),

            // STAT 2 : État des Profils (Métiers)
            Stat::make('Profils Marchands', $profileCount . ' profil(s) actif(s)')
                ->description($profileCount > 0 ? 'Gérez vos activités Upesi' : 'Créez votre premier profil métier')
                ->descriptionIcon('heroicon-m-building-storefront', IconPosition::Before)
                ->color($profileCount > 0 ? 'success' : 'warning')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onClick' => "window.location.href='/app/profiles'",
                ]),


            // STAT 3 : Aide au choix (Cahier des charges)
            Stat::make('Besoin d\'aide ?', 'Choisir mon métier')
                ->description('Quel profil vous correspond ?')
                ->descriptionIcon('heroicon-m-question-mark-circle', IconPosition::Before)
                ->color('info')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    // Déclenche une modal de guide (à définir dans ton layout ou page)
                    'wire:click' => "\$dispatch('open-modal', { id: 'guide-metiers' })",
                ]),
        ];
    }

    // --- Helpers de formatage selon les données réelles ---

    protected function getKycLabel($kyc): string
    {
        if (!$kyc) return 'Non soumis';

        return match ($kyc->status) {
            'approved' => 'Dossier Validé',
            'pending'  => 'Examen en cours',
            'rejected' => 'Dossier Refusé',
            'expired'  => 'Document Expiré',
            default    => 'Incomplet',
        };
    }

    protected function getKycDescription($kyc): string
    {
        if (!$kyc) return 'Obligatoire pour vendre ou acheter';

        return match ($kyc->status) {
            'approved' => 'Votre compte est certifié Upesi',
            'pending'  => 'Validation sous 24h/48h',
            'rejected' => 'Motif : ' . ($kyc->admin_notes ?? 'Inconnu'),
            'expired'  => 'Veuillez renouveler votre pièce',
            default    => 'Cliquez pour régulariser',
        };
    }

    protected function getKycColor($kyc): string
    {
        if (!$kyc) return 'gray';

        return match ($kyc->status) {
            'approved' => 'success',
            'pending'  => 'warning',
            'rejected' => 'danger',
            'expired'  => 'danger',
            default    => 'gray',
        };
    }

    protected function getKycIcon($kyc): string
    {
        return $kyc?->status === 'approved' ? 'heroicon-m-check-badge' : 'heroicon-m-exclamation-triangle';
    }
}
