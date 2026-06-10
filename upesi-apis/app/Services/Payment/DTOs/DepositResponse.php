<?php
namespace App\Services\Payment\DTOs;

class DepositResponse
{
    public function __construct(
        public bool $success,
        public string $transactionId,
        public string $paymentUrl,
        public string $status,
        public ?string $reference = null
    ) {}
}
