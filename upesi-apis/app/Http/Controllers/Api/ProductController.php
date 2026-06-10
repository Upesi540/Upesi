<?php
// app/Http/Controllers/Api/ProductController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Crop;
use App\Models\Category;
use App\Models\Market;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Traits\ResponseFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use ResponseFormat;

    /**
     * Liste paginée des produits avec filtres avancés
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|in:created_at,unit_price,title,popularity',
            'sort_order' => 'nullable|in:asc,desc',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,pending,sold_out',
            'is_featured' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->ResponseUnprocessableEntity(
                'Paramètres de filtrage invalides',
                $validator->errors()
            );
        }

        // Construction de la clé de cache basée sur les filtres
        $cacheKey = 'products_' . md5(json_encode($request->all()));

        // $products = Cache::remember($cacheKey, 300, function () use ($request) {
        $query = Product::query()
            ->with([
                'crop.category',
                'unit',
                'merchantProfile.user:id,first_name,last_name,profile_photo_path',
                'country',
                'state',
                'city'
            ])
            ->where('status', 'active');

        // Filtres par localisation
        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        // Filtres par produit
        if ($request->filled('crop_id')) {
            $query->where('crop_id', $request->crop_id);
        }

        if ($request->filled('category_id')) {
            $query->whereHas('crop', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->filled('market_id')) {
            $query->whereHas('crop.category.market', function ($q) use ($request) {
                $q->where('id', $request->market_id);
            });
        }

        // Filtres par prix
        if ($request->filled('min_price')) {
            $query->where('unit_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('unit_price', '<=', $request->max_price);
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        // Recherche textuelle
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhereHas('crop', function ($cq) use ($search) {
                        $cq->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('scientific_name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'popularity') {
            $query->withCount('orderItems')->orderBy('order_items_count', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);

        $products = $query->paginate($perPage);
        // });

        return $this->ResponseOk(
            'Liste des produits récupérée avec succès',
            ProductResource::collection($products),
            [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        );
    }

    /**
     * Détail d'un produit
     */
    public function show($id)
    {
        $product = Cache::remember("product_{$id}", 60, function () use ($id) {
            return Product::with([
                'crop.category',
                'unit',
                'merchantProfile.user:id,first_name,last_name,profile_photo_path',
                'country',
                'state',
                'city'
            ])->find($id);
        });

        if (!$product) {
            return $this->ResponseNotFound('Produit non trouvé');
        }

        return $this->ResponseOk(
            'Détail du produit récupéré avec succès',
            new ProductResource($product)
        );
    }

    /**
     * Produits par culture (crop)
     */
    // public function byCrop(Request $request, $cropId)
    // {
    //     $crop = Crop::find($cropId);

    //     if (!$crop) {
    //         return $this->ResponseNotFound('Culture non trouvée');
    //     }

    //     $cacheKey = "products_crop_{$cropId}_" . md5(json_encode($request->all()));

    //     $products = Cache::remember($cacheKey, 300, function () use ($request, $cropId) {
    //         $query = Product::with(['crop.category', 'unit', 'merchantProfile.user:id,first_name,last_name,profile_photo_path', 'country', 'state', 'city'])
    //             ->where('crop_id', $cropId)
    //             ->where('status', 'active');

    //         // Appliquer les filtres supplémentaires
    //         $this->applyFilters($query, $request);

    //         return $query->orderBy('created_at', 'desc')
    //             ->paginate($request->get('per_page', 15));
    //     });

    //     return $this->ResponseOk(
    //         "Produits de la culture {$crop->name}",
    //         ProductResource::collection($products),
    //         [
    //             'current_page' => $products->currentPage(),
    //             'last_page' => $products->lastPage(),
    //             'total' => $products->total(),
    //         ]
    //     );
    // }

    // /**
    //  * Produits par catégorie
    //  */
    // public function byCategory(Request $request, $categoryId)
    // {
    //     $category = Category::find($categoryId);

    //     if (!$category) {
    //         return $this->ResponseNotFound('Catégorie non trouvée');
    //     }

    //     $cacheKey = "products_category_{$categoryId}_" . md5(json_encode($request->all()));

    //     $products = Cache::remember($cacheKey, 300, function () use ($request, $categoryId) {
    //         $query = Product::with(['crop.category', 'unit', 'merchantProfile.user:id,first_name,last_name,profile_photo_path', 'country', 'state', 'city'])
    //             ->whereHas('crop', function ($q) use ($categoryId) {
    //                 $q->where('category_id', $categoryId);
    //             })
    //             ->where('status', 'active');

    //         $this->applyFilters($query, $request);

    //         return $query->orderBy('created_at', 'desc')
    //             ->paginate($request->get('per_page', 15));
    //     });

    //     return $this->ResponseOk(
    //         "Produits de la catégorie {$category->name}",
    //         ProductResource::collection($products),
    //         [
    //             'current_page' => $products->currentPage(),
    //             'last_page' => $products->lastPage(),
    //             'total' => $products->total(),
    //         ]
    //     );
    // }

    // /**
    //  * Produits par marché
    //  */
    // public function byMarket(Request $request, $marketId)
    // {
    //     $market = Market::find($marketId);

    //     if (!$market) {
    //         return $this->ResponseNotFound('Marché non trouvé');
    //     }

    //     $cacheKey = "products_market_{$marketId}_" . md5(json_encode($request->all()));

    //     $products = Cache::remember($cacheKey, 300, function () use ($request, $marketId) {
    //         $query = Product::with(['crop.category', 'unit', 'merchantProfile.user:id,first_name,last_name,profile_photo_path', 'country', 'state', 'city'])
    //             ->whereHas('crop.category.market', function ($q) use ($marketId) {
    //                 $q->where('id', $marketId);
    //             })
    //             ->where('status', 'active');

    //         $this->applyFilters($query, $request);

    //         return $query->orderBy('created_at', 'desc')
    //             ->paginate($request->get('per_page', 15));
    //     });

    //     return $this->ResponseOk(
    //         "Produits du marché {$market->name}",
    //         ProductResource::collection($products),
    //         [
    //             'current_page' => $products->currentPage(),
    //             'last_page' => $products->lastPage(),
    //             'total' => $products->total(),
    //         ]
    //     );
    // }

    // /**
    //  * Produits par pays
    //  */
    // public function byCountry(Request $request, $countryId)
    // {
    //     $country = Country::find($countryId);

    //     if (!$country) {
    //         return $this->ResponseNotFound('Pays non trouvé');
    //     }

    //     $cacheKey = "products_country_{$countryId}_" . md5(json_encode($request->all()));

    //     $products = Cache::remember($cacheKey, 300, function () use ($request, $countryId) {
    //         $query = Product::with(['crop.category', 'unit', 'merchantProfile.user:id,first_name,last_name,profile_photo_path', 'country', 'state', 'city'])
    //             ->where('country_id', $countryId)
    //             ->where('status', 'active');

    //         $this->applyFilters($query, $request);

    //         return $query->orderBy('created_at', 'desc')
    //             ->paginate($request->get('per_page', 15));
    //     });

    //     return $this->ResponseOk(
    //         "Produits du pays {$country->name}",
    //         ProductResource::collection($products),
    //         [
    //             'current_page' => $products->currentPage(),
    //             'last_page' => $products->lastPage(),
    //             'total' => $products->total(),
    //         ]
    //     );
    // }

    // /**
    //  * Produits par ville
    //  */
    // public function byCity(Request $request, $cityId)
    // {
    //     $city = City::with('state.country')->find($cityId);

    //     if (!$city) {
    //         return $this->ResponseNotFound('Ville non trouvée');
    //     }

    //     $cacheKey = "products_city_{$cityId}_" . md5(json_encode($request->all()));

    //     $products = Cache::remember($cacheKey, 300, function () use ($request, $cityId) {
    //         $query = Product::with(['crop.category', 'unit', 'merchantProfile.user:id,first_name,last_name,profile_photo_path', 'country', 'state', 'city'])
    //             ->where('city_id', $cityId)
    //             ->where('status', 'active');

    //         $this->applyFilters($query, $request);

    //         return $query->orderBy('created_at', 'desc')
    //             ->paginate($request->get('per_page', 15));
    //     });

    //     return $this->ResponseOk(
    //         "Produits de la ville {$city->name}",
    //         ProductResource::collection($products),
    //         [
    //             'current_page' => $products->currentPage(),
    //             'last_page' => $products->lastPage(),
    //             'total' => $products->total(),
    //         ]
    //     );
    // }

    /**
     * Produits en vedette
     */
    // public function featured(Request $request)
    // {
    //     $cacheKey = 'products_featured_' . md5(json_encode($request->all()));

    //     $products = Cache::remember($cacheKey, 600, function () use ($request) {
    //         $query = Product::with(['crop.category', 'unit', 'merchantProfile.user:id,first_name,last_name,profile_photo_path', 'country', 'state', 'city'])
    //             ->where('is_featured', true)
    //             ->where('status', 'active');

    //         $this->applyFilters($query, $request);

    //         return $query->latest()
    //             ->paginate($request->get('per_page', 12));
    //     });

    //     return $this->ResponseOk(
    //         'Produits en vedette',
    //         ProductResource::collection($products),
    //         [
    //             'current_page' => $products->currentPage(),
    //             'last_page' => $products->lastPage(),
    //             'total' => $products->total(),
    //         ]
    //     );
    // }

    /**
     * Produits similaires
     */
    public function similar(Request $request, $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return $this->ResponseNotFound('Produit non trouvé');
        }

        $cacheKey = "products_similar_{$productId}_" . md5(json_encode($request->all()));

        $similarProducts = Cache::remember($cacheKey, 3600, function () use ($product, $request) {
            return Product::with(['crop.category', 'unit', 'merchantProfile.user:id,first_name,last_name,profile_photo_path'])
                ->where('id', '!=', $product->id)
                ->where('status', 'active')
                ->where(function ($query) use ($product) {
                    $query->where('crop_id', $product->crop_id)
                        ->orWhereHas('crop', function ($q) use ($product) {
                            $q->where('category_id', $product->crop->category_id); // Ou même famille
                        });
                })
                ->inRandomOrder()
                ->limit($request->get('limit', 6))
                ->get();
        });

        return $this->ResponseOk(
            'Produits similaires',
            ProductResource::collection($similarProducts)
        );
    }


    /**
     * Méthode utilitaire pour appliquer les filtres communs
     */
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('min_price')) {
            $query->where('unit_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('unit_price', '<=', $request->max_price);
        }

        if ($request->filled('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        if ($request->filled('crop_id')) {
            $query->where('crop_id', $request->crop_id);
        }

        if ($request->filled('market_id')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('market_id', $request->market_id);
            });
        }

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        return $query;
    }

    /**
     * Méthode utilitaire pour vider le cache des produits
     */
    private function clearProductCache($productId = null)
    {
        if ($productId) {
            Cache::forget("product_{$productId}");
            Cache::forget("products_similar_{$productId}");
        }

        // On ne peut pas vider tous les caches paginés facilement
        // Dans un environnement de production, utilisez Redis ou un système de tags
        if (config('cache.default') === 'redis') {
            Cache::tags(['products'])->flush();
        }
    }
}
