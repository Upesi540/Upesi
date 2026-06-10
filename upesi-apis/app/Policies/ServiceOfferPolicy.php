<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ServiceOffer;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceOfferPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ServiceOffer');
    }

    public function view(AuthUser $authUser, ServiceOffer $serviceOffer): bool
    {
        return $authUser->can('View:ServiceOffer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ServiceOffer');
    }

    public function update(AuthUser $authUser, ServiceOffer $serviceOffer): bool
    {
        return $authUser->can('Update:ServiceOffer');
    }

    public function delete(AuthUser $authUser, ServiceOffer $serviceOffer): bool
    {
        return $authUser->can('Delete:ServiceOffer');
    }

    public function restore(AuthUser $authUser, ServiceOffer $serviceOffer): bool
    {
        return $authUser->can('Restore:ServiceOffer');
    }

    public function forceDelete(AuthUser $authUser, ServiceOffer $serviceOffer): bool
    {
        return $authUser->can('ForceDelete:ServiceOffer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ServiceOffer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ServiceOffer');
    }

    public function replicate(AuthUser $authUser, ServiceOffer $serviceOffer): bool
    {
        return $authUser->can('Replicate:ServiceOffer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ServiceOffer');
    }

}