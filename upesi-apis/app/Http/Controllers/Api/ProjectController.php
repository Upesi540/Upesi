<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Traits\ResponseFormat;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ResponseFormat;

    /**
     * Liste paginée des projets (actifs + triés)
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 12);
        $page = $request->input('page', 1);
        $onlyActive = $request->boolean('active', true);
        $status = $request->input('status'); // 'ongoing', 'completed', etc.

        $query = Project::ordered();

        if ($onlyActive) {
            $query->active();
        }

        if ($status) {
            $query->where('status', $status);
        }

        $projects = $query->paginate($perPage, ['*'], 'page', $page);

        // Construction manuelle des métadonnées de pagination
        $paginationMeta = [
            'current_page' => $projects->currentPage(),
            'last_page'    => $projects->lastPage(),
            'per_page'     => $projects->perPage(),
            'total'        => $projects->total(),
            'from'         => $projects->firstItem(),
            'to'           => $projects->lastItem(),
            'path'         => $projects->path(),
            'links'        => [
                'first' => $projects->url(1),
                'last'  => $projects->url($projects->lastPage()),
                'prev'  => $projects->previousPageUrl(),
                'next'  => $projects->nextPageUrl(),
            ],
        ];

        return $this->ResponseOk(
            'Liste des projets',
            ProjectResource::collection($projects),
            $paginationMeta
        );
    }

    /**
     * Détail d'un projet par son slug ou son ID
     */
    public function show($identifier)
    {
        $project = Project::where('slug', $identifier)
            ->orWhere('id', $identifier)
            ->active()
            ->first();

        if (!$project) {
            return $this->ResponseNotFound('Projet non trouvé');
        }

        return $this->ResponseOk(
            'Détail du projet',
            new ProjectResource($project)
        );
    }
}
