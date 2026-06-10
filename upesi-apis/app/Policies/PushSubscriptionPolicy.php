<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PushSubscription;
use Illuminate\Auth\Access\HandlesAuthorization;

class PushSubscriptionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PushSubscription');
    }

    public function view(AuthUser $authUser, PushSubscription $pushSubscription): bool
    {
        return $authUser->can('View:PushSubscription');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PushSubscription');
    }

    public function update(AuthUser $authUser, PushSubscription $pushSubscription): bool
    {
        return $authUser->can('Update:PushSubscription');
    }

    public function delete(AuthUser $authUser, PushSubscription $pushSubscription): bool
    {
        return $authUser->can('Delete:PushSubscription');
    }

    public function restore(AuthUser $authUser, PushSubscription $pushSubscription): bool
    {
        return $authUser->can('Restore:PushSubscription');
    }

    public function forceDelete(AuthUser $authUser, PushSubscription $pushSubscription): bool
    {
        return $authUser->can('ForceDelete:PushSubscription');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PushSubscription');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PushSubscription');
    }

    public function replicate(AuthUser $authUser, PushSubscription $pushSubscription): bool
    {
        return $authUser->can('Replicate:PushSubscription');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PushSubscription');
    }

}