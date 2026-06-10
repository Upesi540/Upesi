<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\NewsCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class NewsCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:NewsCategory');
    }

    public function view(AuthUser $authUser, NewsCategory $newsCategory): bool
    {
        return $authUser->can('View:NewsCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:NewsCategory');
    }

    public function update(AuthUser $authUser, NewsCategory $newsCategory): bool
    {
        return $authUser->can('Update:NewsCategory');
    }

    public function delete(AuthUser $authUser, NewsCategory $newsCategory): bool
    {
        return $authUser->can('Delete:NewsCategory');
    }

    public function restore(AuthUser $authUser, NewsCategory $newsCategory): bool
    {
        return $authUser->can('Restore:NewsCategory');
    }

    public function forceDelete(AuthUser $authUser, NewsCategory $newsCategory): bool
    {
        return $authUser->can('ForceDelete:NewsCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:NewsCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:NewsCategory');
    }

    public function replicate(AuthUser $authUser, NewsCategory $newsCategory): bool
    {
        return $authUser->can('Replicate:NewsCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:NewsCategory');
    }

}