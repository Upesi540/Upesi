<?php
// app/Http/Controllers/Api/AboutController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Traits\ResponseFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AboutController extends Controller
{
    use ResponseFormat;

    /**
     * Récupère les membres actifs de l'équipe
     */
    public function teamMembers(Request $request)
    {
        $members = TeamMember::active()
            ->ordered()
            ->get();

        return $this->ResponseOk(
            'Équipe récupérée avec succès',
            $members->map(function ($member) {
                return [
                    'id' => $member->id,
                    'first_name' => $member->first_name,
                    'last_name' => $member->last_name,
                    'full_name' => $member->full_name,
                    'role' => $member->role,
                    'bio' => $member->bio,
                    'photo_path' => $member->photo_path ? Storage::url($member->photo_path) : null,
                    'email' => $member->email,
                    'phone' => $member->phone,
                    'social_links' => $member->social_links,
                    'sort_order' => $member->sort_order,
                ];
            })
        );
    }
}
