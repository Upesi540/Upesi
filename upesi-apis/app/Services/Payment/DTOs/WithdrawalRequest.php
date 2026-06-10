<?php

namespace App\Services\Payment\DTOs;

use App\Models\User;

class WithdrawalRequest
{
    public function __construct(
        public User $user,
        public float $amount,
        public string $currency,
        public string $destinationType, // 'mobile_money', 'bank_account'
        public array $destinationDetails, // phone, account_number, etc.
        public array $metadata = []
    ) {}
}

