<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasBulkTransactions;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Notifications\Notification;
use Filament\Panel;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasName, HasAvatar, MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, HasRoles;
    use SoftDeletes;
    use HasBulkTransactions;  // ← Ajouter cette ligne

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'created_by',  // 👈 AJOUTE CECI
        'country_id',
        'preferred_currency_id',
        'phone',
        'email',
        'profile_photo_path',
        'is_active',
        'is_approved',
        'is_banned',
        'password',
        'deleted_by',
        'email_verified_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'full_name',
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_approved' => 'boolean',
            'is_banned' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime'
        ];
    }
    public function canAccessPanel(Panel $panel): bool
    {
        // On vérifie si l'utilisateur est un 'merchant' ou un 'customer'
        $isMerchantOrCustomer = $this->hasRole(['merchant', 'customer']);

        return match ($panel->getId()) {
            'admin' => !$isMerchantOrCustomer, // Accès admin SEULEMENT si PAS merchant ou customer
            'app'   => true,  // Accès app SEULEMENT si merchant ou customer
            default => false,
        };
    }
    public function sendEmailVerificationNotification()
    {
        $this->notify(new class extends VerifyEmail {
            public function toMail($notifiable)
            {
                return (new MailMessage)
                    ->subject('Vérification de votre compte Upesi')
                    ->greeting('Bonjour ' . $notifiable->first_name . ' !')
                    ->line('Merci de vous être inscrit sur Upesi Market. Il ne vous reste qu\'une étape.')
                    ->line('Veuillez cliquer sur le bouton ci-dessous pour valider votre adresse e-mail.')
                    ->action('Vérifier mon compte', $this->verificationUrl($notifiable))
                    ->line('Si vous n\'avez pas créé de compte, vous pouvez ignorer cet e-mail.')
                    ->salutation('L\'équipe Upesi');
            }
        });
    }
    // Dans App\Models\User.php
    public function getQuasarReturnUrl()
    {
        $origin = session('quasar_origin', 'web');

        if ($origin === 'mobile') {
            // On va utiliser 'upesi://' comme nom par défaut
            return 'upesi://home';
        }

        // Retour vers ton site Quasar Web
        return config('app.quasar_url', 'https://u-pesi.com');
    }

    /**
     * Boot du modèle - Création automatique du wallet
     */
    protected static function booted()
    {
        static::created(function ($user) {
            // 1. On récupère la devise du pays de l'user (ou XOF par défaut)
            if (\Spatie\Permission\Models\Role::where('name', 'customer')->exists()) {
                $user->assignRole('customer');
            }
            $currencyCode = config('app.base_currency');

            $currency = Currency::where('code', $currencyCode)->first();

            if ($currency) {
                // 2. Création du premier portefeuille
                // Note: On suppose que createWalletForCurrency retourne l'objet Wallet créé
                $user->createWalletForCurrency($currency->id);

                // 3. Attribution de la devise préférée
                // On utilise updateQuietly pour ne pas déclencher d'autres événements en boucle
                $user->updateQuietly([
                    'preferred_currency_id' => $currency->id,
                ]);
            }
        });
        // 👇 AJOUTE CE BLOC POUR created_by
        // 👇 BLOC POUR created_by
        static::creating(function ($user) {
            // Si created_by n'est pas défini, on utilise l'utilisateur connecté
            if (empty($user->created_by) && Auth::check()) {
                $user->created_by = Auth::id();
            }

            // Si toujours pas de created_by, on laisse NULL
            // (inscription publique ou création en ligne de commande)
        });
    }

    // Ajoute cette méthode utilitaire
    public function isTrader(): bool
    {
        return $this->merchantProfiles()
            ->where('type', 'trader')
            ->exists();
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation : Les utilisateurs créés par cet utilisateur
     */
    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * Crée un wallet pour une devise spécifique
     */
    public function createWalletForCurrency($currencyId)
    {
        return $this->wallet()->create([
            'currency_id' => $currencyId,
            'available_balance' => 0,
            'frozen_balance' => 0,
            'is_active' => true,
        ]);
    }

    /**
     * Cette méthode indique à Filament quelle URL utiliser pour l'avatar
     */
    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->profile_photo_path) {
            return asset('storage/' . $this->profile_photo_path);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&color=FFFFFF&background=FF9100';
    }

    /**
     * Cette méthode pour Filament
     */
    public function getFilamentName(): string
    {
        return $this->full_name;
    }

    /**
     * Accesseur pour le nom complet
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    // /**
    //  * Accesseur pour l'URL de la photo de profil
    //  */
    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo_path) {
            return asset('storage/' . $this->profile_photo_path);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&color=FFFFFF&background=FF9100';
    }

    // ==================== RELATIONS ====================

    /**
     * Pays de l'utilisateur
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Devise préférée de l'utilisateur
     */
    public function preferredCurrency()
    {
        return $this->belongsTo(Currency::class, 'preferred_currency_id');
    }

    /**
     * Utilisateur qui a supprimé cet utilisateur
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Profil utilisateur
     */

    /**
     * Profils d'acteur (vendeur, acheteur, etc.)
     */
    public function merchantProfiles(): HasMany
    {
        return $this->hasMany(MerchantProfile::class);
    }

    /**
     * Adresses de l'utilisateur
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Portefeuille (un par devise)
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }


    /**
     * Transactions sur tous les wallet
     */
    public function walletTransactions(): HasManyThrough
    {
        return $this->hasManyThrough(
            WalletTransaction::class,
            Wallet::class,
            'user_id',
            'wallet_id',
            'id',
            'id'
        );
    }

    /**
     * Produits vendus par l'utilisateur
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'merchant_profile_id');
    }
    public function kyc()
    {
        return $this->hasOne(KycVerification::class);
    }
    public function isKycCompliant(): bool
    {
        $kyc = $this->kycVerification;

        if (!$kyc || $kyc->status !== 'approved') return false;

        // Logique spécifique :
        if ($this->hasRole('transporteur')) {
            return $kyc->document_type === 'driving_license';
        }

        return true;
    }
    /**
     * Commandes passées (en tant qu'acheteur)
     */
    public function ordersAsBuyer(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    /**
     * Commandes reçues (en tant que vendeur)
     */
    public function ordersAsSeller(): HasManyThrough
    {
        return $this->hasManyThrough(
            Order::class,           // Modèle final
            OrderItem::class,       // Modèle intermédiaire
            'merchant_profile_id',  // Clé sur order_items qui pointe vers merchant_profiles
            'id',                   // Clé sur orders
            'id',                   // Clé locale sur merchant_profiles
            'order_id'              // Clé sur order_items qui pointe vers orders
        );
    }

    public function serviceRequestsAsProviderOrTransporter(): HasManyThrough
    {
        return $this->hasManyThrough(ServiceRequest::class, MerchantProfile::class, 'user_id', 'merchant_profile_id')
            ->whereIn('merchant_profiles.type', ['provider', 'transporter'])
            ->where('service_requests.status', 'pending'); // ⚠️ seulement en attente
        ;
    }

    /**
     * Wishlist (produits favoris)
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Produits favoris (via wishlist)
     */
    public function favoriteProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'wishlists', 'user_id', 'product_id')
            ->withTimestamps();
    }

    /**
     * Abonnements push
     */
    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    /**
     * Spéculations de l'utilisateur
     */
    public function crops(): BelongsToMany
    {
        return $this->belongsToMany(Crop::class, 'user_crops', 'user_id', 'crop_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope pour les utilisateurs actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les utilisateurs approuvés
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope pour les utilisateurs bannis
     */
    public function scopeBanned($query)
    {
        return $query->where('is_banned', true);
    }

    /**
     * Scope pour les vendeurs (ont des produits)
     */
    public function scopeSellers($query)
    {
        return $query->whereHas('products');
    }

    /**
     * Scope pour les acheteurs (ont des commandes)
     */
    public function scopeBuyers($query)
    {
        return $query->whereHas('ordersAsBuyer');
    }

    // ==================== MÉTHODES UTILITAIRES ====================

    /**
     * Vérifie si l'utilisateur est un vendeur
     */
    public function isSeller(): bool
    {
        return $this->products()->exists();
    }

    /**
     * Vérifie si l'utilisateur est un acheteur
     */
    public function isBuyer(): bool
    {
        return $this->ordersAsBuyer()->exists();
    }

    /**
     * Récupérer le wallet de l'utilisateur pour une devise donnée
     */
    public function getWalletByCurrency(string $currencyCode): ?Wallet
    {
        $currency = Currency::where('code', $currencyCode)->first();
        if (!$currency) {
            return null;
        }

        return $this->wallets()->where('currency_id', $currency->id)->first();
    }

    public function wallets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Wallet::class);
    }
}
