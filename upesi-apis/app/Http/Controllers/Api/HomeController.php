<?php
// app/Http/Controllers/Api/HomeController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CropResource;
use App\Http\Resources\MarketResource;
use App\Http\Resources\PartnerResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ServiceOfferResource;
use App\Http\Resources\SlideResource;
use App\Models\Category;
use App\Models\Crop;
use App\Models\Market;
use App\Models\Partner;
use App\Models\Product;
use App\Models\ServiceOffer;
use App\Models\Slide;
use App\Models\User;
use App\Traits\ResponseFormat;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    use ResponseFormat;

    public function index()
    {
        // Cache fragmenté
        $slides = Cache::remember(
            'home_slides',
            3600,
            fn() =>
            SlideResource::collection(Slide::where('is_active', true)->orderBy('order')->get())
        );

        $markets = Cache::remember(
            'home_markets',
            1800,
            fn() =>
            MarketResource::collection(Market::where('is_active', true)
                ->with(['categories' => fn($q) => $q->withCount('crops')])
                ->limit(6)
                ->get())
        );


        $popularCrops = Cache::remember(
            'home_popular_crops',
            600,
            fn() =>
            CropResource::collection(Crop::where('is_active', true)
                ->with(['category:id,name,slug', 'defaultUnit'])
                ->withCount('products')
                ->orderBy('products_count', 'desc')
                ->limit(12)
                ->get())
        );

        // 1. On récupère un "réservoir" de produits en cache
        $productPool = Cache::remember(
            'home_featured_pool',
            600,
            fn() => Product::where('is_featured', true)
                ->where('status', 'active')
                ->with([
                    'crop.category:id,name,slug',
                    'unit',
                    'merchantProfile.user:id,first_name,last_name,profile_photo_path' // ← correction
                ])
                ->latest()
                ->limit(60)
                ->get()
        );

        // 2. On mélange et on en prend 12 au hasard parmi les 30 (Côté PHP, donc ultra rapide)
        $randomSelection = $productPool->random(min($productPool->count(), 12));

        // 3. On transforme en ressource pour l'API
        $featuredProducts = ProductResource::collection($randomSelection);


        // === OFFRES DE SERVICE ALÉATOIRES ===
        $servicePool = Cache::remember(
            'home_service_offers_pool',
            600, // 10 minutes
            fn() => ServiceOffer::where('status', 'active')
                ->where('is_available', true)
                ->with([
                    'service.category',
                    'merchantProfile.user',
                ])
                ->latest()
                ->limit(30) // on prend les 30 dernières offres actives
                ->get()
        );

        $randomServices = $servicePool->random(min($servicePool->count(), 8));
        $featuredServices = ServiceOfferResource::collection($randomServices);
        // Stats agricoles pertinentes
        $stats = Cache::remember('home_agriculture_stats', now()->addDays(3), function () {
            $totalProducts = Product::where('status', 'active')->count();
            $activeFarmers = User::role('merchant')->count();

            return [
                'total_products' => $totalProducts,
                'active_markets' => Market::where('is_active', true)->count(),
                'product_categories' => Category::where('is_active', true)->count(),
                'crop_varieties' => Crop::where('is_active', true)->count(),
                'active_farmers' => $activeFarmers,
                'active_buyers' => User::role('customer')->count(),
                'avg_products_per_farmer' => $activeFarmers > 0 ? round($totalProducts / $activeFarmers, 1) : 0,
            ];
        });


        $partners = Cache::remember('home_partners', 3600, fn() =>
        PartnerResource::collection(Partner::featured()
            ->active()
            ->orderBy('sort_order')
            ->get()));

        return $this->ResponseOk('home', [
            'slides' => $slides,
            'markets' => $markets,
            'popular_crops' => $popularCrops,
            'featured_products' => $featuredProducts,
            'featured_services' => $featuredServices,
            'stats' => $stats,
            // 'testimonials' => $testimonials,
            'partners' => $partners,
        ]);
    }
}
