<?php
// app/Http/Controllers/Api/LegalController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegalDocument;
use App\Traits\ResponseFormat;
use Illuminate\Http\Request;

class LegalController extends Controller
{
    use ResponseFormat;

    /**
     * Récupère un document légal par son slug
     */
    public function show($slug)
    {
        $document = LegalDocument::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$document) {
            return $this->ResponseNotFound('Document non trouvé');
        }

        return $this->ResponseOk('Document récupéré', [
            'id' => $document->id,
            'title' => $document->title,
            'slug' => $document->slug,
            'version' => $document->version,
            'content' => $document->content, // déjà un tableau JSON
            'updated_at' => $document->updated_at,
        ]);
    }
}
