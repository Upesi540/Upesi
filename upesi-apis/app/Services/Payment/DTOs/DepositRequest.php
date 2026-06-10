<?php
namespace App\Services\Payment\DTOs;

use App\Models\User;

class DepositRequest
{
    public function __construct(
        public User $user,
        public float $amount,
        public string $currency,
        public array $metadata = []
    ) {}
}
