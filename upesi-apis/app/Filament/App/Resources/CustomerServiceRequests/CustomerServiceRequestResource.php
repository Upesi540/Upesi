<?php

namespace App\Filament\App\Resources\CustomerServiceRequests;

use App\Filament\App\Resources\CustomerServiceRequests\Pages\ManageCustomerServiceRequests;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Models\MerchantProfile;
use App\Models\ServiceRequest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class CustomerServiceRequestResource extends Resource
{
    protected static ?string $model = ServiceRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Mes demandes de service';

    protected static ?string $modelLabel = 'demande';

    protected static ?string $pluralModelLabel = 'demandes';

    /**
     * Traduction des statuts
     */
    protected static function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'En attente',
            'accepted' => 'Accepté',
            'rejected' => 'Rejeté',
            'in_progress' => 'En cours',
            'completed' => 'Terminé',
            'cancelled' => 'Annulé',
            default => $status,
        };
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('buyer_id', Auth::id())
            ->where('status', 'pending')
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('buyer_id', Auth::id())
            ->with(['merchantProfile', 'serviceOffer.service']);
    }

    public static function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make('Informations générales')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('request_number')
                                    ->label('N° demande')
                                    ->weight('bold'),
                                TextEntry::make('merchantProfile.shop_name')
                                    ->label('Prestataire')
                                    ->formatStateUsing(fn($record) =>
                                        $record->merchantProfile->shop_name .
                                        ' (' . MerchantProfile::TYPES[$record->merchantProfile->type] . ')'
                                    ),
                                TextEntry::make('serviceOffer.title')->label('Service demandé'),
                                TextEntry::make('serviceOffer.service.name')->label('Type'),
                                TextEntry::make('status')
                                    ->label('Statut')
                                    ->badge()
                                    ->formatStateUsing(fn(string $state): string => self::getStatusLabel($state))
                                    ->color(fn(string $state): string => match ($state) {
                                        'pending'    => 'warning',
                                        'accepted'   => 'info',
                                        'rejected'   => 'danger',
                                        'in_progress'=> 'primary',
                                        'completed'  => 'success',
                                        'cancelled'  => 'danger',
                                        default      => 'gray',
                                    }),
                                TextEntry::make('quoted_price')->label('Devis')->money('XOF'),
                                TextEntry::make('final_price')->label('Prix final')->money('XOF'),
                            ]),
                    ]),

                Section::make('Détails de la demande')
                    ->schema([
                        TextEntry::make('description')->label('Description')->markdown(),
                        TextEntry::make('details')
                            ->label('Détails')
                            ->formatStateUsing(fn($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state),
                    ]),

                Section::make('Dates')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('scheduled_at')->label('Date prévue')->dateTime(),
                                TextEntry::make('started_at')->label('Débuté le')->dateTime(),
                                TextEntry::make('completed_at')->label('Terminé le')->dateTime(),
                                TextEntry::make('cancelled_at')->label('Annulé le')->dateTime(),
                            ]),
                    ]),

                Section::make('Annulation')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('cancelled_by')
                                    ->label('Annulé par')
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'buyer' => 'Moi-même',
                                        'provider' => 'Le prestataire',
                                        'admin' => 'Administrateur',
                                        default => null,
                                    }),
                                TextEntry::make('cancellation_reason')->label('Motif'),
                            ])
                            ->visible(fn($record) => $record->status === 'cancelled'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                TextColumn::make('request_number')
                    ->label('N° demande')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('merchantProfile.shop_name')
                    ->label('Prestataire')
                    ->formatStateUsing(fn($record) =>
                        $record->merchantProfile->shop_name .
                        ' (' . MerchantProfile::TYPES[$record->merchantProfile->type] . ')'
                    )
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('merchantProfile', function ($q) use ($search) {
                            $q->where('shop_name', 'like', "%{$search}%")
                              ->orWhere('type', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('serviceOffer.title')
                    ->label('Service')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => self::getStatusLabel($state))
                    ->color(fn(string $state): string => match ($state) {
                        'pending'    => 'warning',
                        'accepted'   => 'info',
                        'rejected'   => 'danger',
                        'in_progress'=> 'primary',
                        'completed'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    }),
                TextColumn::make('quoted_price')
                    ->label('Devis')
                    ->money('XOF')
                    ->sortable(),
                TextColumn::make('scheduled_at')
                    ->label('Prévue le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Demandée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending'    => 'En attente',
                        'accepted'   => 'Accepté',
                        'rejected'   => 'Rejeté',
                        'in_progress'=> 'En cours',
                        'completed'  => 'Terminé',
                        'cancelled'  => 'Annulé',
                    ]),
            ])
            ->actions([
                // Voir détails
                ViewAction::make()
                    ->modalHeading('Détails de la demande')
                    ->modalWidth('5xl'),

                // Annuler la demande (via contrôleur)
                Action::make('cancel')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => in_array($record->status, ['pending', 'accepted']))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Motif d\'annulation')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (ServiceRequest $record, array $data) {
                        try {
                            $controller = app(ServiceRequestController::class);
                            $request = request()->merge(['reason' => $data['reason']]);
                            $response = $controller->cancelRequest($record->id, $request);

                            $responseData = json_decode($response->getContent(), true);

                            if ($response->getStatusCode() === 200) {
                                Notification::make()
                                    ->title('Demande annulée')
                                    ->body('Votre demande a été annulée et vous êtes remboursé.')
                                    ->success()
                                    ->send();
                                redirect(request()->header('Referer'));
                            } else {
                                Notification::make()
                                    ->title('Erreur')
                                    ->body($responseData['message'] ?? 'Une erreur est survenue')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // Confirmer la réalisation (quand le service est terminé)
                Action::make('confirm_completion')
                    ->label('Confirmer la réalisation')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'in_progress')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmer la réalisation du service')
                    ->modalDescription('Confirmez-vous que le service a été correctement réalisé ? Le paiement sera libéré au prestataire.')
                    ->action(function (ServiceRequest $record) {
                        try {
                            $controller = app(ServiceRequestController::class);
                            $response = $controller->confirmCompletion($record->id);

                            $responseData = json_decode($response->getContent(), true);

                            if ($response->getStatusCode() === 200) {
                                Notification::make()
                                    ->title('Service confirmé')
                                    ->body('Merci pour votre confirmation. Le paiement a été libéré.')
                                    ->success()
                                    ->send();
                                redirect(request()->header('Referer'));
                            } else {
                                Notification::make()
                                    ->title('Erreur')
                                    ->body($responseData['message'] ?? 'Une erreur est survenue')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomerServiceRequests::route('/'),
        ];
    }
}
