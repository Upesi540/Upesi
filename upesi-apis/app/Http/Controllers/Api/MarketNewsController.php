<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MarketNewsResource;
use App\Http\Resources\NewsCategoryResource;
use App\Models\MarketNews;
use App\Models\NewsCategory;
use App\Traits\ResponseFormat;
use Illuminate\Http\Request;

class MarketNewsController extends Controller
{
    use ResponseFormat;
    public function categories()
    {
        $categories = NewsCategory::where('is_active', true)->orderBy('sort_order')->get();
        return $this->ResponseOk('Liste des catégories', NewsCategoryResource::collection($categories));
    }
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 12);
        $page = $request->input('page', 1);

        $query = MarketNews::with(['author', 'newsCategory'])
            ->where('is_active', true)
            ->where('published_at', '<=', now())
            ->notExpired();

        // Filtre par catégorie
        if ($request->filled('category_slug')) {
            $category = NewsCategory::where('slug', $request->category_slug)->first();
            if ($category) {
                $query->where('news_category_id', $category->id);
            }
        }

        // Filtre par type (flash, normal, etc.)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Recherche textuelle (titre, contenu, excerpt)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('excerpt', 'LIKE', "%{$search}%")
                    ->orWhere('content', 'LIKE', "%{$search}%");
            });
        }

        // Tri : pinned en premier, puis date de publication
        $query->orderBy('is_pinned', 'desc')
            ->orderBy('published_at', 'desc');

        $news = $query->paginate($perPage, ['*'], 'page', $page);

        $paginationMeta = [
            'current_page' => $news->currentPage(),
            'last_page'    => $news->lastPage(),
            'per_page'     => $news->perPage(),
            'total'        => $news->total(),
            'from'         => $news->firstItem(),
            'to'           => $news->lastItem(),
            'path'         => $news->path(),
            'links'        => [
                'first' => $news->url(1),
                'last'  => $news->url($news->lastPage()),
                'prev'  => $news->previousPageUrl(),
                'next'  => $news->nextPageUrl(),
            ],
        ];

        return $this->ResponseOk(
            'Liste des actualités',
            MarketNewsResource::collection($news),
            $paginationMeta
        );
    }

    public function show($identifier)
    {
        $news = MarketNews::with(['author', 'newsCategory'])
            ->where(function ($q) use ($identifier) {
                $q->where('slug', $identifier)->orWhere('id', $identifier);
            })
            ->where('is_active', true)
            ->where('published_at', '<=', now())
            ->notExpired()
            ->first();

        if (!$news) {
            return $this->ResponseNotFound('Actualité non trouvée');
        }

        // Incrémenter les vues (optionnel)
        // $news->increment('views');

        return $this->ResponseOk(
            'Détail de l\'actualité',
            new MarketNewsResource($news)
        );
    }
}
