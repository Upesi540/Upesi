<?php

namespace App\Services\Payment\DTOs;


// app/Services/Payment/DTOs/WithdrawalResponse.php
class WithdrawalResponse
{
    public function __construct(
        public bool $success,
        public string $withdrawalId,
        public string $status,
        public ?string $message = null
    ) {}
}

