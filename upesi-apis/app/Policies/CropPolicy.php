<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Crop;
use Illuminate\Auth\Access\HandlesAuthorization;

class CropPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Crop');
    }

    public function view(AuthUser $authUser, Crop $crop): bool
    {
        return $authUser->can('View:Crop');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Crop');
    }

    public function update(AuthUser $authUser, Crop $crop): bool
    {
        return $authUser->can('Update:Crop');
    }

    public function delete(AuthUser $authUser, Crop $crop): bool
    {
        return $authUser->can('Delete:Crop');
    }

    public function restore(AuthUser $authUser, Crop $crop): bool
    {
        return $authUser->can('Restore:Crop');
    }

    public function forceDelete(AuthUser $authUser, Crop $crop): bool
    {
        return $authUser->can('ForceDelete:Crop');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Crop');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Crop');
    }

    public function replicate(AuthUser $authUser, Crop $crop): bool
    {
        return $authUser->can('Replicate:Crop');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Crop');
    }

}