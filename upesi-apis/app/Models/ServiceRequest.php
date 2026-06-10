<?php

namespace App\Models;

use App\Helpers\ReferenceGenerator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRequest extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'request_number',
        'buyer_id',
        'merchant_profile_id',
        'service_offer_id',
        'status',
        'description',
        'details',
        'quoted_price',
        'final_price',
        'scheduled_at',
        'started_at',
        'completed_at',
        'cancelled_by',
        'cancellation_reason',
        'cancelled_at',
        'accepted_at',
        'rejected_at',
        'currency_id',
    ];

    protected $casts = [
        'details' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'quoted_price' => 'decimal:2',
        'final_price' => 'decimal:2',
    ];

    // ==================== RELATIONS ====================

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function merchantProfile()
    {
        return $this->belongsTo(MerchantProfile::class);
    }

    public function serviceOffer()
    {
        return $this->belongsTo(ServiceOffer::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    // ==================== ACCESSORS ====================

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'En attente',
            'accepted' => 'Accepté',
            'rejected' => 'Rejeté',
            'in_progress' => 'En cours',
            'completed' => 'Terminé',
            'cancelled' => 'Annulé',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'accepted' => 'info',
            'rejected' => 'danger',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    public function getCancelledByLabelAttribute(): ?string
    {
        return match ($this->cancelled_by) {
            'buyer' => 'Client',
            'provider' => 'Prestataire',
            'admin' => 'Administrateur',
            default => null,
        };
    }

    // ==================== SCOPES ====================

    public function scopeForBuyer($query, $userId)
    {
        return $query->where('buyer_id', $userId);
    }

    public function scopeForProvider($query, $merchantProfileId)
    {
        return $query->where('merchant_profile_id', $merchantProfileId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['completed', 'cancelled', 'rejected']);
    }

    // ==================== METHODS ====================

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'accepted']);
    }

    public function canBeAccepted(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeRejected(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeStarted(): bool
    {
        return $this->status === 'accepted';
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'in_progress';
    }

    // ==================== BOOT ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (!$request->request_number) {
                $request->request_number = ReferenceGenerator::generate('SRV', 8);
            }
        });
    }


}
