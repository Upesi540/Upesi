<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class KycVerification extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'entity_type',
        'document_type',
        'document_number',
        'document_files',
        'selfie_path',

        // --- Nouveaux champs Business (Gabon) ---
        'rccm_number',
        'rccm_path',
        'cfe_card_path',
        'quitus_fiscal_path',
        'nif_number',

        // --- Infos Magasin & Géo ---
        'store_description',
        'latitude',
        'longitude',
        'google_maps_url',

        // --- État & Dates ---
        'status',
        'admin_notes',
        'verified_at',
        'expiry_date',
        'approved_by', // L'ID de l'admin qui a validé
    ];

    protected $casts = [
        'document_files' => 'array',
        'verified_at' => 'datetime',
        'expiry_date' => 'date',
        'status' => 'string',
    ];

    // --- Constantes ---
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';

    // --- Relations ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * L'administrateur qui a traité ce dossier
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // --- Logic métier ---

    protected static function booted(): void
    {
        static::updating(function (KycVerification $kyc) {
            // On détecte si le statut a changé
            if ($kyc->isDirty('status')) {

                // Automatisme : Enregistrer qui a fait l'action

                if ($kyc->status === self::STATUS_APPROVED) {
                    $kyc->verified_at = now();
                    $kyc->admin_notes = null;

                    // ACTION : Activer tous les profils marchands de l'utilisateur
                    $kyc->user->merchantProfiles()->update([
                        'status' => 'approved',
                        'approved_by' => Auth::user()->id
                    ]);
                }

                if ($kyc->status === self::STATUS_REJECTED || $kyc->status === self::STATUS_EXPIRED) {
                    // ACTION : Désactiver/Suspendre les profils marchands
                    $kyc->user->merchantProfiles()->update([
                        'status' => 'rejected',
                        'approved_by' => Auth::user()->id

                    ]);
                }
            }
        });
    }

    // --- Helpers ---

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isExpired(): bool
    {
        if (!$this->expiry_date) return false;
        return $this->expiry_date->isPast();
    }
}
