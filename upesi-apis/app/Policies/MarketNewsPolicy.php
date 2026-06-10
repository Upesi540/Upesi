<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MarketNews;
use Illuminate\Auth\Access\HandlesAuthorization;

class MarketNewsPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MarketNews');
    }

    public function view(AuthUser $authUser, MarketNews $marketNews): bool
    {
        return $authUser->can('View:MarketNews');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MarketNews');
    }

    public function update(AuthUser $authUser, MarketNews $marketNews): bool
    {
        return $authUser->can('Update:MarketNews');
    }

    public function delete(AuthUser $authUser, MarketNews $marketNews): bool
    {
        return $authUser->can('Delete:MarketNews');
    }

    public function restore(AuthUser $authUser, MarketNews $marketNews): bool
    {
        return $authUser->can('Restore:MarketNews');
    }

    public function forceDelete(AuthUser $authUser, MarketNews $marketNews): bool
    {
        return $authUser->can('ForceDelete:MarketNews');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MarketNews');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MarketNews');
    }

    public function replicate(AuthUser $authUser, MarketNews $marketNews): bool
    {
        return $authUser->can('Replicate:MarketNews');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MarketNews');
    }

}