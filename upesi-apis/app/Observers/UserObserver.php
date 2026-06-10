<?php
// app/Observers/UserObserver.php

namespace App\Observers;

use App\Models\User;
use App\Mail\WelcomeMail;
use App\Mail\AlertLoginMail;
use App\Mail\NewUserAdminMail;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Quand un nouvel utilisateur est créé
     */
    public function created(User $user): void
    {
        Log::info('👤 UserObserver::created déclenché', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name
        ]);

        // 1. Email de bienvenue à l'utilisateur
        try {
            Mail::to($user->email, $user->name)->send(new WelcomeMail($user));
            Log::info('Email de bienvenue envoyé', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email de bienvenue', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }

        // 2. Notification Filament à l'utilisateur
        try {
            Notification::make()
                ->title('🎉 Bienvenue !')
                ->body('Votre compte a été créé avec succès.')
                ->success()
                ->sendToDatabase($user);
            Log::info('Notification Filament bienvenue envoyée', ['user_id' => $user->id]);
        } catch (\Exception $e) {
            Log::error('Erreur notification Filament bienvenue', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }

        // 3. Notifier les admins
        $this->notifyAdmins($user);

        Log::info('UserObserver::created terminé', ['user_id' => $user->id]);
    }

    /**
     * Notifier tous les admins
     */
    protected function notifyAdmins(User $newUser): void
    {
        Log::info('Notification aux admins pour nouvel utilisateur', [
            'new_user_id' => $newUser->id,
            'new_user_email' => $newUser->email
        ]);

        $admins = User::role('admin')->get();

        Log::info('Nombre d\'admins trouvés', [
            'count' => $admins->count(),
            'admin_ids' => $admins->pluck('id')->toArray()
        ]);

        if ($admins->isEmpty()) {
            Log::warning('Aucun admin trouvé pour notification', [
                'new_user_id' => $newUser->id
            ]);
            return;
        }

        foreach ($admins as $admin) {
            try {
                // Notification Filament à l'admin
                Notification::make()
                    ->title('🆕 Nouvel utilisateur inscrit')
                    ->body("{$newUser->name} ({$newUser->email}) vient de s'inscrire")
                    ->icon('heroicon-o-user-plus')
                    ->sendToDatabase($admin);
                Log::info('Notification Filament envoyée à l\'admin', [
                    'admin_id' => $admin->id,
                    'admin_email' => $admin->email,
                    'new_user_id' => $newUser->id
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur notification Filament à l\'admin', [
                    'admin_id' => $admin->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Notification de connexion (appelée manuellement)
     */
    public function sendLoginNotification(User $user, string $ip, string $device, bool $isNewDevice = false): void
    {
        Log::info('🔐 sendLoginNotification appelé', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip' => $ip,
            'device' => $device,
            'is_new_device' => $isNewDevice
        ]);

        // Email
        try {
            Mail::to($user->email, $user->name)->send(new AlertLoginMail($user, $ip, $device, $isNewDevice));
            Log::info('Email alerte connexion envoyé', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email alerte connexion', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }

        // Notification Filament
        try {
            Notification::make()
                ->title('🔐 Nouvelle connexion')
                ->body("Connexion le " . now()->format('d/m/Y H:i') . " depuis {$device}")
                ->info()
                ->sendToDatabase($user);
            Log::info('Notification Filament alerte connexion envoyée', [
                'user_id' => $user->id,
                'device' => $device
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur notification Filament alerte connexion', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
