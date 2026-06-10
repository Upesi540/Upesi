<?php

namespace App\Filament\App\Resources\BulkTransactions\Schemas;

use App\Models\Crop;
use App\Models\MerchantProfile;
use App\Models\Product;
use App\Models\User;
use App\Services\Payment\Services\CommissionService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class BulkTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        $isEdit = $schema->getOperation() === 'edit';
        $record = $schema->getRecord();
        $isLocked = $record && in_array($record->status, ['pending', 'approved', 'completed', 'rejected']);

        return $schema
            ->components([
                Section::make('Informations générales')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('type')
                                    ->label('Type de transaction')
                                    ->options([
                                        'sale' => '🛒 Vente groupée (Client ← Vendeurs)',
                                        'purchase' => '📦 Achat groupé (Acheteurs → Fournisseur)',
                                    ])
                                    ->required()
                                    ->disabled($isEdit)
                                    ->live()
                                    ->afterStateUpdated(function ($set) {
                                        $set('status', 'draft');
                                        $set('total_amount', 0);
                                        $set('trader_commission', 0);
                                    }),

                                Select::make('counterparty_id')
                                    ->label(function ($get) {
                                        return $get('type') === 'sale' ? '👤 Client final' : '🏭 Fournisseur final';
                                    })
                                    ->options(function ($get) {
                                        $user = Auth::user();
                                        $type = $get('type');

                                        if ($type === 'sale') {
                                            return User::where('created_by', $user->id)
                                                ->whereDoesntHave('merchantProfiles')
                                                ->get()
                                                ->mapWithKeys(fn($u) => [$u->id => $u->first_name . ' ' . ($u->last_name ?? '')]);
                                        }

                                        return User::where('created_by', $user->id)
                                            ->whereHas('merchantProfiles', fn($q) => $q->where('type', 'supplier'))
                                            ->get()
                                            ->mapWithKeys(fn($u) => [$u->id => $u->first_name . ' ' . ($u->last_name ?? '')]);
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disabled($isEdit),

                                Hidden::make('trader_id')
                                    ->default(fn() => Auth::id()),
                            ]),
                    ]),

                Section::make('Produits et participants')
                    ->schema([
                        // 🔥 D'ABORD choisir le PRODUIT (Crop)
                        Select::make('selected_crop_id')
                            ->label('🌾 Sélectionner un produit')
                            ->options(Crop::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($set, $state) {
                                $set('current_product_name', Crop::find($state)?->name ?? '');
                            })
                            ->helperText('Choisissez d\'abord le produit que vous voulez regrouper'),

                        // 🔥 ENSUITE le repeater pour les participants
                        Repeater::make('details')
                            ->relationship('details')
                            ->label('Participants')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('merchant_profile_id')
                                            ->label(function ($get) use ($isLocked) {
                                                $type = $get('type') ?? 'sale';
                                                return $type === 'sale' ? '👥 Vendeur' : '👥 Acheteur';
                                            })
                                            ->options(function ($get) use ($isLocked) {
                                                $user = Auth::user();
                                                $type = $get('type') ?? 'sale';
                                                $currentProductName = $get('../../current_product_name') ?? '';

                                                // 🔥 Filtrer les participants qui ont ce produit en stock
                                                $query = MerchantProfile::whereHas('user', function ($q) use ($user) {
                                                    $q->where('created_by', $user->id);
                                                })->whereIn('type', ['producer', 'supplier']);

                                                if ($type === 'sale' && !empty($currentProductName)) {
                                                    // Pour une vente: participants qui ont ce produit en stock
                                                    $query->whereHas('products', function ($q) use ($currentProductName) {
                                                        $q->where('title', 'like', "%{$currentProductName}%")
                                                            ->orWhereHas('crop', function ($cq) use ($currentProductName) {
                                                                $cq->where('name', $currentProductName);
                                                            });
                                                    });
                                                }

                                                return $query->get()
                                                    ->mapWithKeys(fn($p) => [
                                                        $p->id => ($p->user?->first_name ?? '') . ' - ' .
                                                                  ($p->type === 'producer' ? '🌾 Producteur' : '🏭 Fournisseur') .
                                                                  ($p->shop_name ? ' (' . $p->shop_name . ')' : '')
                                                    ]);
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->disabled($isLocked)
                                            ->live(),

                                        Hidden::make('product_name')
                                            ->default(fn($get) => $get('../../current_product_name') ?? ''),
                                    ]),

                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('quantity')
                                            ->label('Quantité')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0.01)
                                            ->live()
                                            ->disabled($isLocked)
                                            ->afterStateUpdated(fn($set, $get) => self::recalculateLine($set, $get)),

                                        TextInput::make('unit')
                                            ->label('Unité')
                                            ->default('kg')
                                            ->maxLength(20)
                                            ->disabled($isLocked),

                                        TextInput::make('unit_price')
                                            ->label('Prix unitaire (FCFA)')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0)
                                            ->live()
                                            ->disabled($isLocked)
                                            ->afterStateUpdated(fn($set, $get) => self::recalculateLine($set, $get)),
                                    ]),

                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('subtotal')
                                            ->label('Sous-total')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(true)
                                            ->prefix('FCFA'),

                                        TextInput::make('commission_rate')
                                            ->label('Commission (%)')
                                            ->numeric()
                                            ->default(5.0)
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->live()
                                            ->disabled($isLocked)
                                            ->afterStateUpdated(fn($set, $get) => self::recalculateLine($set, $get)),

                                        TextInput::make('participant_gets')
                                            ->label('Net participant')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(true)
                                            ->prefix('FCFA'),
                                    ]),

                                Hidden::make('commission_amount'),
                                Hidden::make('participant_type')
                                    ->default(fn($get) => ($get('type') ?? 'sale') === 'sale' ? 'seller' : 'buyer'),
                            ])
                            ->minItems(1)
                            ->addActionLabel('➕ Ajouter un participant pour ce produit')
                            ->columnSpanFull()
                            ->live()
                            ->disabled($isLocked)
                            ->afterStateUpdated(fn($state, $set) => self::recalculateTotal($state, $set)),
                    ]),

                Section::make('Résumé financier')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('total_amount')
                                    ->label('💰 Montant total')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->prefix('FCFA'),
                                TextInput::make('trader_commission')
                                    ->label('🏆 Votre commission (2%)')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->prefix('FCFA')
                                    ->helperText('Commission automatique de 2% sur le montant total'),
                            ]),
                    ]),

                Section::make('Suivi')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Placeholder::make('statut')
                                    ->label('Statut')
                                    ->content(fn($record) => $record?->status_label ?? 'Brouillon'),
                                Placeholder::make('valide_le')
                                    ->label('Validé le')
                                    ->content(fn($record) => $record?->validated_at?->format('d/m/Y H:i') ?? '-'),
                                Placeholder::make('valide_par')
                                    ->label('Validé par')
                                    ->content(fn($record) => $record?->validatedBy?->first_name ?? '-'),
                            ]),
                    ])
                    ->visible($isEdit),

                Hidden::make('current_product_name'),
                Hidden::make('status')->default('draft'),
            ]);
    }

    protected static function recalculateLine($set, $get): void
    {
        $quantity = floatval($get('quantity') ?? 0);
        $price = floatval($get('unit_price') ?? 0);
        $subtotal = $quantity * $price;
        $rate = floatval($get('commission_rate') ?? 0);
        $commission = ($subtotal * $rate) / 100;

        $set('subtotal', round($subtotal, 2));
        $set('commission_amount', round($commission, 2));
        $set('participant_gets', round($subtotal - $commission, 2));
    }

    protected static function recalculateTotal($state, $set): void
    {
        $total = 0;
        foreach ($state as $item) {
            $total += floatval($item['subtotal'] ?? 0);
        }
        $commissionService = app(CommissionService::class);
        $set('total_amount', round($total, 2));
        $set('trader_commission', $commissionService->calculateTraderCommission($total));
    }
}
