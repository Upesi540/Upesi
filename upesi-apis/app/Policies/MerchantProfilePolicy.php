<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MerchantProfile;
use Illuminate\Auth\Access\HandlesAuthorization;

class MerchantProfilePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MerchantProfile');
    }

    public function view(AuthUser $authUser, MerchantProfile $merchantProfile): bool
    {
        return $authUser->can('View:MerchantProfile');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MerchantProfile');
    }

    public function update(AuthUser $authUser, MerchantProfile $merchantProfile): bool
    {
        return $authUser->can('Update:MerchantProfile');
    }

    public function delete(AuthUser $authUser, MerchantProfile $merchantProfile): bool
    {
        return $authUser->can('Delete:MerchantProfile');
    }

    public function restore(AuthUser $authUser, MerchantProfile $merchantProfile): bool
    {
        return $authUser->can('Restore:MerchantProfile');
    }

    public function forceDelete(AuthUser $authUser, MerchantProfile $merchantProfile): bool
    {
        return $authUser->can('ForceDelete:MerchantProfile');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MerchantProfile');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MerchantProfile');
    }

    public function replicate(AuthUser $authUser, MerchantProfile $merchantProfile): bool
    {
        return $authUser->can('Replicate:MerchantProfile');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MerchantProfile');
    }

}