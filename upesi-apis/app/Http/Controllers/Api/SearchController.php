<?php
// app/Http/Controllers/Api/SearchController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ServiceOfferResource;
use App\Models\Product;
use App\Models\ServiceOffer;
use App\Traits\ResponseFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    use ResponseFormat;

    /**
     * Recherche globale : produits ET services
     */
    public function search(Request $request)
    {
        // Log des paramètres reçus
        Log::info('SearchController@search - REQUEST', [
            'q' => $request->get('q'),
            'type' => $request->get('type'),
            'per_page' => $request->get('per_page'),
            'all_params' => $request->all(),
        ]);

        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:1|max:255',
            'per_page' => 'nullable|integer|min:1|max:50',
            'type' => 'nullable|in:all,products,services',
        ]);

        if ($validator->fails()) {
            Log::warning('SearchController@search - Validation failed', ['errors' => $validator->errors()]);
            return $this->ResponseUnprocessableEntity('Paramètres invalides', $validator->errors());
        }

        $query = $request->get('q');
        $type = $request->get('type', 'all');
        $perPage = $request->get('per_page', 20);

        $cacheKey = 'search_' . md5($query . '_' . $type . '_' . $perPage);

        $results = Cache::remember($cacheKey, 300, function () use ($query, $type, $perPage) {
            Log::info('SearchController@search - Cache miss, computing results', ['query' => $query, 'type' => $type]);
            $response = [];

            if ($type === 'all' || $type === 'products') {
                $response['products'] = $this->searchProducts($query, $perPage);
                Log::info('SearchController@search - Products computed', ['count' => $response['products']['total'] ?? 0]);
            }

            if ($type === 'all' || $type === 'services') {
                $response['services'] = $this->searchServices($query, $perPage);
                Log::info('SearchController@search - Services computed', ['count' => $response['services']['total'] ?? 0]);
            }

            return $response;
        });

        // Log de la structure avant envoi
        Log::info('SearchController@search - FINAL RESPONSE STRUCTURE', [
            'has_products' => isset($results['products']),
            'has_services' => isset($results['services']),
            'products_total' => $results['products']['total'] ?? null,
            'services_total' => $results['services']['total'] ?? null,
        ]);

        return $this->ResponseOk('Résultats de recherche', $results);
    }

    /**
     * Recherche de produits
     */
    private function searchProducts(string $query, int $perPage): array
    {
        Log::info('SearchController@searchProducts - START', ['query' => $query, 'per_page' => $perPage]);

        $products = Product::query()
            ->with([
                'crop.category',
                'unit',
                'merchantProfile.user:id,first_name,last_name,profile_photo_path',
                'country',
                'state',
                'city'
            ])
            ->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%")
                    ->orWhere('sku', 'LIKE', "%{$query}%")
                    ->orWhereHas('crop', function ($cq) use ($query) {
                        $cq->where('name', 'LIKE', "%{$query}%")
                            ->orWhere('scientific_name', 'LIKE', "%{$query}%");
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        Log::info('SearchController@searchProducts - SQL executed', [
            'total_products' => $products->total(),
            'first_title' => $products->first()?->title,
        ]);

        return [
            'data' => ProductResource::collection($products),
            'total' => $products->total(),
            'per_page' => $products->perPage(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
        ];
    }

    /**
     * Recherche de services (offres)
     */
    private function searchServices(string $query, int $perPage): array
    {
        Log::info('SearchController@searchServices - START', ['query' => $query, 'per_page' => $perPage]);

        $services = ServiceOffer::query()
            ->active()
            ->with(['merchantProfile.user', 'service.category'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%")
                    ->orWhereHas('service', function ($sq) use ($query) {
                        $sq->where('name', 'LIKE', "%{$query}%");
                    })
                    ->orWhereHas('service.category', function ($cq) use ($query) {
                        $cq->where('name', 'LIKE', "%{$query}%");
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        Log::info('SearchController@searchServices - SQL executed', [
            'total_services' => $services->total(),
            'first_title' => $services->first()?->title,
        ]);

        return [
            'data' => ServiceOfferResource::collection($services),
            'total' => $services->total(),
            'per_page' => $services->perPage(),
            'current_page' => $services->currentPage(),
            'last_page' => $services->lastPage(),
        ];
    }

    /**
     * Suggestions automatiques (autocomplete)
     */
    public function autocomplete(Request $request)
    {
        Log::info('SearchController@autocomplete - REQUEST', ['q' => $request->get('q')]);

        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:1|max:255',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        if ($validator->fails()) {
            return $this->ResponseUnprocessableEntity('Paramètres invalides', $validator->errors());
        }

        $query = $request->get('q');
        $limit = $request->get('limit', 10);

        $cacheKey = 'search_autocomplete_' . md5($query);

        $suggestions = Cache::remember($cacheKey, 300, function () use ($query, $limit) {
            Log::info('SearchController@autocomplete - Cache miss', ['query' => $query]);

            $productSuggestions = Product::where('status', 'active')
                ->where('title', 'LIKE', "%{$query}%")
                ->limit($limit)
                ->get(['id', 'title', 'images'])
                ->map(fn($p) => [
                    'id' => $p->id,
                    'title' => $p->title,
                    'type' => 'product',
                    'image' => $p->images[0] ?? null,
                ]);

            $serviceSuggestions = ServiceOffer::active()
                ->where('title', 'LIKE', "%{$query}%")
                ->limit($limit)
                ->get(['id', 'title', 'images'])
                ->map(fn($s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'type' => 'service',
                    'image' => $s->images[0] ?? null,
                ]);

            $merged = $productSuggestions->merge($serviceSuggestions)->take($limit);
            Log::info('SearchController@autocomplete - Suggestions found', ['count' => $merged->count()]);
            return $merged;
        });

        return $this->ResponseOk('Suggestions', $suggestions);
    }
}
