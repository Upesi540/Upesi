<?php

namespace App\Filament\App\Resources\MerchantServiceRequests;

use App\Filament\App\Resources\MerchantServiceRequests\Pages\ManageMerchantServiceRequests;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Models\MerchantProfile;
use App\Models\ServiceRequest;
use App\Traits\HasProfileBasedAccess;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
use UnitEnum;

class MerchantServiceRequestResource extends Resource
{
    protected static ?string $model = ServiceRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string|UnitEnum|null $navigationGroup = 'Prestation & Logistique';

    protected static ?string $navigationLabel = 'Demandes reçues';

    protected static ?string $modelLabel = 'demande';

    protected static ?string $pluralModelLabel = 'demandes';

    use HasProfileBasedAccess;

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

    public static function canAccess(): bool
    {
        return (new static())->canAccessResource(['provider', 'transporter']);
    }

    public static function getNavigationBadge(): ?string
    {
        $profileIds = MerchantProfile::where('user_id', Auth::id())->pluck('id');
        return static::getModel()::whereIn('merchant_profile_id', $profileIds)
            ->where('status', 'pending')
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        $profileIds = MerchantProfile::where('user_id', Auth::id())->pluck('id');
        return parent::getEloquentQuery()
            ->whereIn('merchant_profile_id', $profileIds)
            ->with(['buyer', 'serviceOffer.service']);
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
                                TextEntry::make('buyer.name')
                                    ->label('Client'),
                                TextEntry::make('buyer.email')
                                    ->label('Email client'),
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
                                        'in_progress' => 'primary',
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
                            ]),
                    ]),

                Section::make('Annulation')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('cancelled_by')
                                    ->label('Annulé par')
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'buyer' => 'Le client',
                                        'provider' => 'Moi-même',
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
                // TextColumn::make('buyer.last_name')
                //     ->label('Client Nom')
                //     ->searchable(),
                // TextColumn::make('buyer.first_name')
                //     ->label('Client')
                //     ->searchable(),
                TextColumn::make('buyer.email')
                    ->label('Email')
                    ->searchable(),
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
                        'in_progress' => 'primary',
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
                // TextColumn::make('created_at')
                //     ->label('Reçue le')
                //     ->dateTime('d/m/Y H:i')
                //     ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending'    => 'En attente',
                        'accepted'   => 'Accepté',
                        'rejected'   => 'Rejeté',
                        'in_progress' => 'En cours',
                        'completed'  => 'Terminé',
                        'cancelled'  => 'Annulé',
                    ]),
            ])
            ->actions([
                // Voir détails
                ViewAction::make()
                    ->modalHeading('Détails de la demande')
                    ->modalWidth('5xl'),

                // Accepter la demande
                Action::make('accept')
                    ->label('Accepter')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Accepter la demande')
                    ->modalDescription('Confirmez-vous que vous souhaitez accepter cette demande ?')
                    ->action(function (ServiceRequest $record) {
                        try {
                            $controller = app(ServiceRequestController::class);
                            $response = $controller->acceptRequest($record->id);

                            $responseData = json_decode($response->getContent(), true);

                            if ($response->getStatusCode() === 200) {
                                Notification::make()
                                    ->title('Demande acceptée')
                                    ->body('Vous avez accepté cette demande.')
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

                // Rejeter la demande
                Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('reason')
                            ->label('Motif du rejet')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (ServiceRequest $record, array $data) {
                        try {
                            $controller = app(ServiceRequestController::class);
                            $request = request()->merge(['reason' => $data['reason']]);
                            $response = $controller->rejectRequest($record->id, $request);

                            $responseData = json_decode($response->getContent(), true);

                            if ($response->getStatusCode() === 200) {
                                Notification::make()
                                    ->title('Demande rejetée')
                                    ->body('Vous avez rejeté cette demande. Le client est remboursé.')
                                    ->warning()
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

                // Démarrer le service
                Action::make('start')
                    ->label('Démarrer')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->visible(fn($record) => $record->status === 'accepted')
                    ->requiresConfirmation()
                    ->action(function (ServiceRequest $record) {
                        try {
                            $controller = app(ServiceRequestController::class);
                            $response = $controller->markAsStarted($record->id);

                            $responseData = json_decode($response->getContent(), true);

                            if ($response->getStatusCode() === 200) {
                                Notification::make()
                                    ->title('Service démarré')
                                    ->body('Le service a été marqué comme démarré.')
                                    ->info()
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

                // Terminer le service (libère le paiement)
                Action::make('complete')
                    ->label('Terminer')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'in_progress')
                    ->requiresConfirmation()
                    ->modalHeading('Terminer le service')
                    ->modalDescription('Confirmez-vous que le service est terminé ? Le paiement sera libéré.')
                    ->form([
                        // TextInput::make('final_price')
                        //     ->label('Prix final (FCFA)')
                        //     ->numeric()
                        //     ->prefix('FCFA')
                        //     ->default(fn($record) => $record->quoted_price),
                        // Textarea::make('completion_notes')
                        //     ->label('Notes de réalisation')
                        //     ->rows(2),
                    ])
                    ->action(function (ServiceRequest $record, array $data) {
                        try {
                            $controller = app(ServiceRequestController::class);
                            $response = $controller->markAsCompleted($record->id);

                            $responseData = json_decode($response->getContent(), true);

                            if ($response->getStatusCode() === 200) {
                                // Mettre à jour le prix final si différent
                                if (isset($data['final_price']) && $data['final_price'] != $record->final_price) {
                                    $record->update(['final_price' => $data['final_price']]);
                                }

                                Notification::make()
                                    ->title('Service terminé')
                                    ->body('Le service a été marqué comme terminé. Le paiement est libéré.')
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
            'index' => ManageMerchantServiceRequests::route('/'),
        ];
    }
}
