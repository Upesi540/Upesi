<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return ['Laravel' => app()->version()];
// });
// OU si ton panel s'appelle spécifiquement "app" :
Route::redirect('/', '/app');
Route::get('/auth/autologin', [AuthenticatedSessionController::class, 'autologin'])->name('autologin');
// require __DIR__.'/auth.php';
// Route de retour après paiement (Humain)
Route::get('/payment/callback/{gateway}', [PaymentController::class, 'callback'])
    ->name('payment.callback');

Route::view('/supprimer-compte', 'delete-account')->name('account.delete');

Route::get('/legal/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
