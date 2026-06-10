<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MarketResource;
use App\Http\Resources\ServiceCategoryResource; // À créer
use App\Models\Market;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\Cache;
use App\Traits\ResponseFormat;

class AppInitController extends Controller
{
     use ResponseFormat;

    public function index()
    {
        // On utilise une clé unique et descriptive
        $data= Cache::remember('upesi_global_navigation', 3600, function () {
            return [
                // 1. La Bourse (Marchés > Catégories)
                'markets' => MarketResource::collection(
                    Market::where('is_active', true)
                        ->with('categories')
                        ->orderBy('sort_order')
                        ->get()
                ),

                // 2. Les Prestations (Catégories de services > Services)
                'service_categories' => ServiceCategoryResource::collection(
                    ServiceCategory::where('is_active', true)
                        ->with(['services' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
                        ->orderBy('sort_order')
                        ->get()
                ),


            ];

        });
        return $this->ResponseOk('navigation',$data);
    }
}
