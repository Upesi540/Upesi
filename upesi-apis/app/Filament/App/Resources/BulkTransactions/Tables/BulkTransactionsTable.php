<?php

namespace App\Filament\App\Resources\BulkTransactions\Tables;

use App\Models\BulkTransaction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class BulkTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->getStateUsing(fn($record) => 'BT-' . substr($record->id, 0, 8))
                    ->copyable()
                    ->searchable(),

                TextColumn::make('type_label')
                    ->label('Type')
                    ->badge()
                    ->icon(fn($record) => $record->type === 'sale' ? 'heroicon-m-shopping-cart' : 'heroicon-m-truck')
                    ->color(fn($record) => $record->type === 'sale' ? 'success' : 'info'),

                TextColumn::make('counterparty.first_name')
                    ->label(fn($record) => $record?->type === 'sale' ? 'Client' : 'Fournisseur')
                    ->formatStateUsing(fn($record) => $record->counterparty?->first_name ?? '-')
                    ->searchable(),
                TextColumn::make('total_amount')
                    ->label('Montant')
                    ->money('XOF')
                    ->sortable(),

                TextColumn::make('trader_commission')
                    ->label('Commission')
                    ->money('XOF')
                    ->color('warning'),

                TextColumn::make('details_count')
                    ->label('Lignes')
                    ->counts('details')
                    ->alignCenter(),

                TextColumn::make('status_label')
                    ->label('Statut')
                    ->badge()
                    ->color(fn($record) => $record->statusColor),

                TextColumn::make('validated_at')
                    ->label('Validé le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'sale' => '🛒 Ventes groupées',
                        'purchase' => '📦 Achats groupés',
                    ]),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                ViewAction::make()
                    ->label('Voir'),

                EditAction::make()
                    ->label('Modifier')
                    ->visible(fn($record) => in_array($record->status, ['draft', 'pending'])),

                Action::make('submit')
                    ->label('Soumettre')
                    ->icon('heroicon-m-paper-airplane')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Soumettre pour validation')
                    ->modalDescription('Une fois soumise, vous ne pourrez plus modifier cette transaction.')
                    ->visible(fn($record) => $record->status === 'draft' && $record->details()->count() > 0)
                    ->action(fn($record) => $record->submitForValidation()),

                Action::make('complete')
                    ->label('Compléter')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'approved')  // ← Visible seulement si approuvé
                    ->action(function ($record) {
                        $record->complete();  // 🔥 ICI on appelle la méthode

                        Notification::make()
                            ->title('Transaction complétée')
                            ->body('Le stock a été mis à jour')
                            ->success()
                            ->send();
                    }),

                Action::make('delete')
                    ->label('Supprimer')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'draft')
                    ->action(fn($record) => $record->delete()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn() => false),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->deferLoading()
            ->striped();
    }
}
