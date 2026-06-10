<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema; // Attention : Assurez-vous que votre version utilise Schema ou Form
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WalletsRelationManager extends RelationManager
{
    protected static string $relationship = 'wallet';
    protected static ?string $title = 'Portefeuilles Numériques';
    protected static ?string $modelLabel = 'Portefeuille';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('currency_id')
                    ->label('Devise')
                    ->relationship(
                        name: 'currency',
                        titleAttribute: 'code',
                        modifyQueryUsing: fn(Builder $query) => $query->orderBy('code'),
                    )
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->code} - {$record->name}")
                    ->searchable(['code', 'name'])
                    ->preload()
                    ->required()
                    ->disabled(fn(string $operation): bool => $operation === 'edit'),

                TextInput::make('available_balance')
                    ->label('Solde Disponible')
                    ->numeric()
                    ->prefix('Σ')
                    ->disabled()
                    ->default(0.0),

                TextInput::make('frozen_balance')
                    ->label('Solde Gelé')
                    ->numeric()
                    ->prefix('❄️')
                    ->disabled()
                    ->default(0.0),

                Toggle::make('is_active')
                    ->label('Compte Actif')
                    ->onColor('success')
                    ->default(true)
                    // Sécurité : On empêche de désactiver le portefeuille principal
                    // ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
        ->deferLoading()
            ->columns([
                TextColumn::make('currency.code')
                    ->label('Devise')
                    ->badge()
                    // On change la couleur selon le rôle du portefeuille
                    ->color(fn($record) => match (true) {
                        $record->currency_id === $this->getOwnerRecord()->preferred_currency_id => 'success', // Vert pour la Vente Locale
                        default => 'info',
                    })
                    ->icon(fn($record) => match (true) {
                        $record->currency_id === $this->getOwnerRecord()->preferred_currency_id => 'heroicon-m-shopping-cart',
                        default => null,
                    })
                    ->description(function ($record) {
                        $owner = $this->getOwnerRecord();
                        $isPreferred = $record->currency_id === $owner->preferred_currency_id;

                        if ($isPreferred) return '⭐ Portefeuille favori';
                        return null;
                    }),

                TextColumn::make('available_balance')
                    ->label('Disponible')
                    ->money(fn($record) => $record->currency->code ?? config('app.base_currency'))
                    ->color('success')
                    ->weight('bold'),

                TextColumn::make('frozen_balance')
                    ->label('Gelé')
                    ->money(fn($record) => $record->currency->code ?? config('app.base_currency'))
                    ->color('warning'),

                IconColumn::make('is_active')
                    ->label('Statut')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nouveau Portefeuille')
                    ->before(function (array $data, CreateAction $action) {
                        $exists = $this->getOwnerRecord()->wallets()
                            ->where('currency_id', $data['currency_id'])
                            ->exists();
                        if ($exists) {
                            \Filament\Notifications\Notification::make()
                                ->title('Erreur')
                                ->body('Cet utilisateur possède déjà un portefeuille dans cette devise.')
                                ->danger()
                                ->send();
                            $action->halt();
                        }
                    }),
            ])
            ->recordActions([
                // Action rapide pour changer le portefeuille principal
                // Action::make('set_primary')
                //     ->label('Définir comme principal')
                //     ->icon('heroicon-m-star')
                //     ->color('warning')
                //     // On l'affiche seulement si ce n'est pas déjà le principal
                //     ->hidden(fn($record) => $record->id === $this->getOwnerRecord()->primary_wallet_id || !$record->is_active)
                //     ->tooltip('Définir comme portefeuille par défaut')
                //     ->requiresConfirmation()
                //     ->action(function ($record) {
                //         $user = $this->getOwnerRecord();
                //         $user->primary_wallet_id = $record->id;
                //         $user->save();

                //         \Filament\Notifications\Notification::make()
                //             ->title('Succès')
                //             ->body("Le portefeuille {$record->currency->code} est désormais le principal.")
                //             ->success()
                //             ->send();
                //     }),

                EditAction::make()
                    ->label('Gérer')
                    ->modalHeading('Gérer le portefeuille'),
            ]);
    }

    public function getEloquentQuery(): Builder
    {
        // On preload la currency et on trie pour mettre le principal en haut
        return parent::getEloquentQuery()
            ->with(['currency'])
            ->orderByRaw("id = ? DESC", [$this->getOwnerRecord()->primary_wallet_id])
            ->orderBy('created_at', 'desc');
    }
}
