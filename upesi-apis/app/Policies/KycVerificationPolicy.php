<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KycVerification;
use Illuminate\Auth\Access\HandlesAuthorization;

class KycVerificationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KycVerification');
    }

    public function view(AuthUser $authUser, KycVerification $kycVerification): bool
    {
        return $authUser->can('View:KycVerification');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KycVerification');
    }

    public function update(AuthUser $authUser, KycVerification $kycVerification): bool
    {
        return $authUser->can('Update:KycVerification');
    }

    public function delete(AuthUser $authUser, KycVerification $kycVerification): bool
    {
        return $authUser->can('Delete:KycVerification');
    }

    public function restore(AuthUser $authUser, KycVerification $kycVerification): bool
    {
        return $authUser->can('Restore:KycVerification');
    }

    public function forceDelete(AuthUser $authUser, KycVerification $kycVerification): bool
    {
        return $authUser->can('ForceDelete:KycVerification');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KycVerification');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KycVerification');
    }

    public function replicate(AuthUser $authUser, KycVerification $kycVerification): bool
    {
        return $authUser->can('Replicate:KycVerification');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KycVerification');
    }

}