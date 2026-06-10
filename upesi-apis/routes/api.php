<?php

use App\Http\Controllers\Api\AboutController;
use App\Http\Controllers\Api\AppInitController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CropController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\LegalController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\MarketAnalysisController;
use App\Http\Controllers\Api\MarketController;
use App\Http\Controllers\Api\MarketNewsController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\ServiceOfferController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\SlideController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PaymentController;

use Illuminate\Support\Facades\Route;

// Versioning API [citation:4]
Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        require __DIR__ . '/auth.php';
    });

    // routes/api.php
    Route::get('/app-status', function () {
        return response()->json([
            'version' => '0.0.7', // Change ça à chaque déploiement
            'force_clear' => true
        ]);
    });

    // 🏠 PAGE D'ACCUEIL - UNE SEULE REQUÊTE
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/navigation', [AppInitController::class, 'index']);
    Route::get('/market/ticker', [MarketAnalysisController::class, 'ticker']);
    // routes/api.php

    Route::get('/legal/{slug}', [LegalController::class, 'show']);
    // Products
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        // Route::get('/featured', [ProductController::class, 'featured']);
        // Route::get('/by-crop/{cropId}', [ProductController::class, 'byCrop']);
        // Route::get('/by-category/{categoryId}', [ProductController::class, 'byCategory']);
        // Route::get('/by-market/{marketId}', [ProductController::class, 'byMarket']);
        // Route::get('/by-country/{countryId}', [ProductController::class, 'byCountry']);
        // Route::get('/by-city/{cityId}', [ProductController::class, 'byCity']);
        Route::get('/{id}/similar', [ProductController::class, 'similar']);
        Route::get('/{id}', [ProductController::class, 'show']);
    });
    Route::get('/service-categories', [ServiceOfferController::class, 'serviceCategories']);
    Route::prefix('service-offers')->group(function () {
        // Liste avec pagination et filtres
        Route::get('/', [ServiceOfferController::class, 'index']);
        // Zones disponibles
        Route::get('/zones', [ServiceOfferController::class, 'getZones']);
        // Offres en vedette
        Route::get('/featured', [ServiceOfferController::class, 'getFeatured']);
        // Offres par catégorie
        Route::get('/category/{categorySlug}', [ServiceOfferController::class, 'getByCategory']);
        // Offres par service
        Route::get('/service/{serviceSlug}', [ServiceOfferController::class, 'getByService']);
        // Détail d'une offre
        Route::get('/{id}', [ServiceOfferController::class, 'show']);
    });
    Route::get('/about/team', [AboutController::class, 'teamMembers']);

    Route::apiResource('markets', MarketController::class)->only(['index', 'show']);
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
    Route::apiResource('crops', CropController::class)->only(['index', 'show']);
    Route::apiResource('slides', SlideController::class)->only(['index', 'show']);
    Route::get('projects', [ProjectController::class, 'index']);
    Route::get('projects/{identifier}', [ProjectController::class, 'show']);
    Route::get('news-categories', [MarketNewsController::class, 'categories']);
    Route::get('news', [MarketNewsController::class, 'index']);
    Route::get('news/{identifier}', [MarketNewsController::class, 'show']);
    // routes/api.php
    Route::prefix('search')->group(function () {
        Route::get('/', [SearchController::class, 'search']);
        Route::get('/autocomplete', [SearchController::class, 'autocomplete']);
    });
    // Auth routes (protégées)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('auth/me', [UserController::class, 'me']);
        Route::post('/auth/generate-magic-link', [AuthenticatedSessionController::class, 'generateMagicLink']);
        Route::middleware(['verified'])->group(function () {
            Route::post('/orders', [OrderController::class, 'store']);
            // Liste des gateways disponibles
            // Route::get('/payment/gateways', [PaymentController::class, 'gateways']);
            // Dépôts
            // Route::post('/payment/deposit', [PaymentController::class, 'deposit']);
            // Retraits
            // Route::post('/payment/withdraw', [PaymentController::class, 'withdraw']);
            // Statut d'une transaction
            Route::get('/payment/{gateway}/status/{transactionId}', [PaymentController::class, 'status']);


            // Service Requests
            Route::prefix('service-requests')->group(function () {
                Route::post('/', [ServiceRequestController::class, 'store']);
                // Route::get('/', [ServiceRequestController::class, 'index']);
                // Route::get('/seller', [ServiceRequestController::class, 'sellerRequests']);
                // Route::get('/{id}', [ServiceRequestController::class, 'show']);

                // Actions
                // Route::post('/{id}/cancel', [ServiceRequestController::class, 'cancelRequest']);
                // Route::post('/{id}/accept', [ServiceRequestController::class, 'acceptRequest']);
                // Route::post('/{id}/reject', [ServiceRequestController::class, 'rejectRequest']);
                // Route::post('/{id}/start', [ServiceRequestController::class, 'markAsStarted']);
                // Route::post('/{id}/complete', [ServiceRequestController::class, 'markAsCompleted']);
                // Route::post('/{id}/confirm', [ServiceRequestController::class, 'confirmCompletion']);
            });
        });
    });



    // 🌍 RESSOURCES ANNEXES (avec cache long)
    // api.php
    Route::prefix('locations')->group(function () {
        // Récupérer tous les pays
        Route::get('/countries', [LocationController::class, 'getCountries']);
        // Récupérer les états d'un pays spécifique
        Route::get('/countries/{countryId}/states', [LocationController::class, 'getStatesByCountry']);
        // OPTIONNEL: Pays avec ses états en une requête
        Route::get('/countries/{countryId}/with-states', [LocationController::class, 'getCountryWithStates']);
        // Version tout-en-un (recommandée pour votre frontend)
        Route::get('/locations', [LocationController::class, 'getLocations']);
    });

    // // 💰 DEVISES
    // Route::get('/currencies', [CurrencyController::class, 'index']);
    // Route::get('/currencies/{id}', [CurrencyController::class, 'show']);
    // Route::get('/currencies/{id}/convert', [CurrencyController::class, 'convert']); // Pour conversions
});
// Webhooks (publics)
Route::post('/payment/webhook/{gateway}', [PaymentController::class, 'webhook'])
    ->name('payment.webhook');
