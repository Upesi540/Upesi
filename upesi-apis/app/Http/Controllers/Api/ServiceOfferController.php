<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceCategoryResource;
use App\Http\Resources\ServiceOfferResource;
use App\Models\Service;
use App\Models\ServiceOffer;
use App\Models\ServiceCategory;
use App\Traits\ResponseFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ServiceOfferController extends Controller
{
    use ResponseFormat;

    /**
     * Liste des offres avec cache et filtres
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|in:created_at,price,title',
            'sort_order' => 'nullable|in:asc,desc',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'merchant_type' => 'nullable|in:transporter,provider',
        ]);

        if ($validator->fails()) {
            return $this->ResponseUnprocessableEntity('Filtres invalides', $validator->errors());
        }

        // Clé de cache unique par combinaison de filtres
        $cacheKey = 'service_offers_' . md5(json_encode($request->all()));

        $offers = Cache::remember($cacheKey, 300, function () use ($request) {
            $query = ServiceOffer::query()
                ->active()
                ->with(['merchantProfile.user', 'service.category']);

            $this->applyFilters($query, $request);

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            return $query->orderBy($sortBy, $sortOrder)
                ->paginate($request->get('per_page', 15));
        });

        return $this->ResponseOk(
            'Offres de service récupérées',
            ServiceOfferResource::collection($offers),
            $this->getPaginationMeta($offers)
        );
    }

    /**
     * Détail d'une offre
     */
    public function show($id)
    {
        $offer = Cache::remember("service_offer_{$id}", 60, function () use ($id) {
            return ServiceOffer::with(['merchantProfile.user', 'service.category'])
                ->active()
                ->find($id);
        });

        if (!$offer) {
            return $this->ResponseNotFound('Offre non trouvée');
        }

        return $this->ResponseOk('Détail de l\'offre récupéré', new ServiceOfferResource($offer));
    }
    public function serviceCategories()
    {
        $categories = ServiceCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return $this->ResponseOk(
            'Liste des catégories de services',
            ServiceCategoryResource::collection($categories)
        );
    }
    public function getByService(Request $request, $serviceSlug)
    {
        $service = Service::where('slug', $serviceSlug)->first();
        if (!$service) return $this->ResponseNotFound('Service non trouvé');

        $offers = ServiceOffer::active()
            ->with(['merchantProfile.user', 'service.category'])
            ->where('service_id', $service->id)
            ->paginate($request->get('per_page', 15));

        return $this->ResponseOk("Offres pour le service {$service->name}", ServiceOfferResource::collection($offers), $this->getPaginationMeta($offers));
    }

    /**
     * Offres par catégorie
     */
    public function getByCategory(Request $request, $categorySlug)
    {
        $category = ServiceCategory::where('slug', $categorySlug)->first();
        if (!$category) return $this->ResponseNotFound('Catégorie non trouvée');

        $cacheKey = "services_cat_{$categorySlug}_" . md5(json_encode($request->all()));

        $offers = Cache::remember($cacheKey, 300, function () use ($request, $categorySlug) {
            $query = ServiceOffer::active()
                ->with(['merchantProfile.user', 'service.category'])
                ->whereHas('service.category', fn($q) => $q->where('slug', $categorySlug));

            $this->applyFilters($query, $request);

            return $query->latest()->paginate($request->get('per_page', 15));
        });

        return $this->ResponseOk("Offres de la catégorie {$category->name}", ServiceOfferResource::collection($offers), $this->getPaginationMeta($offers));
    }

    /**
     * Zones disponibles (pour les filtres du front)
     */
    public function getZones()
    {
        $zones = Cache::remember('service_zones_unique', 3600, function () {
            return ServiceOffer::active()
                ->whereNotNull('service_zones')
                ->get()
                ->flatMap(fn($offer) => $offer->service_zones ?? [])
                ->unique()
                ->sort()
                ->values();
        });

        return $this->ResponseOk('Zones récupérées', $zones);
    }

    /**
     * Offres en vedette
     */
    public function getFeatured(Request $request)
    {
        $limit = $request->get('limit', 10);
        $offers = Cache::remember("services_featured_{$limit}", 600, function () use ($limit) {
            return ServiceOffer::active()
                ->where('is_featured', true)
                ->with(['merchantProfile.user', 'service.category'])
                ->latest()
                ->limit($limit)
                ->get();
        });

        return $this->ResponseOk('Offres en vedette', ServiceOfferResource::collection($offers));
    }

    /**
     * MÉTHODE PRIVÉE : Application des filtres (Factorisation)
     */
    private function applyFilters($query, Request $request)
    {
        // Recherche textuelle
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filtre par Service spécifique
        if ($request->filled('service')) {
            $query->whereHas('service', function ($q) use ($request) {
                $q->where('slug', $request->service)->orWhere('id', $request->service);
            });
        }

        // Filtre JSON Zones
        if ($request->filled('zone')) {
            $query->whereJsonContains('service_zones', $request->zone);
        }

        // Filtres Prix
        if ($request->filled('min_price')) $query->where('price', '>=', $request->min_price);
        if ($request->filled('max_price')) $query->where('price', '<=', $request->max_price);

        // Type de marchand (Transporteur / Prestataire)
        if ($request->filled('merchant_type')) {
            $query->whereHas('merchantProfile', fn($q) => $q->where('type', $request->merchant_type));
        }

        return $query;
    }

    /**
     * MÉTHODE PRIVÉE : Formatage méta pagination
     */
    private function getPaginationMeta($paginated)
    {
        return [
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
        ];
    }
}
