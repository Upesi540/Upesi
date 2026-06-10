<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PriceHistory;
use Illuminate\Auth\Access\HandlesAuthorization;

class PriceHistoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PriceHistory');
    }

    public function view(AuthUser $authUser, PriceHistory $priceHistory): bool
    {
        return $authUser->can('View:PriceHistory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PriceHistory');
    }

    public function update(AuthUser $authUser, PriceHistory $priceHistory): bool
    {
        return $authUser->can('Update:PriceHistory');
    }

    public function delete(AuthUser $authUser, PriceHistory $priceHistory): bool
    {
        return $authUser->can('Delete:PriceHistory');
    }

    public function restore(AuthUser $authUser, PriceHistory $priceHistory): bool
    {
        return $authUser->can('Restore:PriceHistory');
    }

    public function forceDelete(AuthUser $authUser, PriceHistory $priceHistory): bool
    {
        return $authUser->can('ForceDelete:PriceHistory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PriceHistory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PriceHistory');
    }

    public function replicate(AuthUser $authUser, PriceHistory $priceHistory): bool
    {
        return $authUser->can('Replicate:PriceHistory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PriceHistory');
    }

}