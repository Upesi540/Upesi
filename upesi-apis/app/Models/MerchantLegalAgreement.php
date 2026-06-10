<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantLegalAgreement extends Model
{
    use HasUuids;

    protected $fillable = [
        'merchant_profile_id',
        'legal_document_id',
        'agreement_type',
        'version',
        'ip_address',
        'user_agent', // Ajouté ici
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
        ];
    }

    // 1. La relation vers le document (ESSENTIEL pour afficher le titre dans Filament)
    public function legalDocument(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class);
    }

    public function merchantProfile(): BelongsTo
    {
        return $this->belongsTo(MerchantProfile::class);
    }
}
