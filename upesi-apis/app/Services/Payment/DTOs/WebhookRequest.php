<?php
namespace App\Services\Payment\DTOs;

class WebhookRequest
{
    public function __construct(
        public array $payload,
        public array $headers,
        public string $rawContent // ⬅️ On ajoute le JSON brut ici
    ) {}
}
