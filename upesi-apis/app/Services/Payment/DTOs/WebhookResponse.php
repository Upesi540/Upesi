<?php

namespace App\Services\Payment\DTOs;

class WebhookResponse
{
    public function __construct(
        public bool $processed,
        public string $status,
        public ?string $message = null
    ) {}
}
