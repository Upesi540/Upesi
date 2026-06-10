<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Observers\OrderObserver;
use App\Observers\ServiceRequestObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url') . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        // Enregistrer l'Observer ici au lieu d'EventServiceProvider
        User::observe(UserObserver::class);
        Order::observe(OrderObserver::class);  // ← Ajoute cette ligne
        ServiceRequest::observe(ServiceRequestObserver::class);  // ← Ajoute cette ligne

    }
}
