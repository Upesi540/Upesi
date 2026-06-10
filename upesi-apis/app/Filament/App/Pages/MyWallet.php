<?php

namespace App\Filament\App\Pages;

use App\Models\Transaction;
use App\Services\Payment\DTOs\DepositRequest;
use App\Services\Payment\Services\PaymentGatewayManager;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MyWallet extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationLabel = 'Mon Portefeuille';
    protected static ?string $title = 'Gestion du Portefeuille';
    protected string $view = 'filament.app.pages.my-wallet';
    protected static ?int $navigationSort = 1;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('recharger_wallet')
                ->label('Recharger')
                ->icon('heroicon-o-credit-card')
                ->schema([
                    Select::make('gateway')
                        ->label('Moyen de paiement')
                        ->options([
                            'fedapay' => 'FedaPay (CFA)',
                            // 'stripe' => 'Stripe (International)', // Prêt pour le futur
                        ])
                        ->default('fedapay')
                        ->required(),
                    TextInput::make('amount')
                        ->label('Montant')
                        ->numeric()
                        ->required(),
                    Select::make('currency')
                        ->label('Devise')
                        ->options(['XOF' => 'XOF'])
                        ->default('XOF'),
                ])
                // ... dans getHeaderActions ou emptyStateActions
                ->action(function (array $data, PaymentGatewayManager $gatewayManager) {
                    try {
                        // On récupère l'user connecté et son wallet
                        $user = Auth::user();
                        $wallet = $user->wallet; // Assure-toi que la relation est définie dans ton modèle User

                        if (!$wallet) {
                            throw new \Exception("Vous ne possédez pas encore de portefeuille actif.");
                        }

                        $gateway = $gatewayManager->getGateway($data['gateway']);

                        $depositRequest = new DepositRequest(
                            user: $user, // Plus de $record->user, on utilise $user directement
                            amount: $data['amount'],
                            currency: $data['currency'],
                            metadata: [
                                'wallet_id' => $wallet->id,
                                'source' => 'Upesi App'
                            ]
                        );

                        $response = $gateway->initiateDeposit($depositRequest);

                        if ($response->success) {
                            return redirect()->away($response->paymentUrl);
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erreur')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Transaction::query()->where('user_id', Auth::user()->id)->orderBy('created_at', 'desc'))
            ->heading('Historique des activités')
            ->description('Suivez vos dépôts et vos dépenses sur la bourse.')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color('gray'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'deposit' => 'Dépôt',
                        'withdrawal' => 'Retrait',
                        'payment' => 'Achat',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'deposit' => 'success',
                        'withdrawal' => 'danger',
                        'payment' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('amount')
                    ->label('Montant')
                    ->money(config('app.base_currency'))
                    ->weight('bold')
                    ->color(fn($record) => $record->type === 'deposit' ? 'success' : 'gray'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type de transaction')
                    ->options([
                        'deposit' => 'Dépôts',
                        'payment' => 'Achats',
                        'withdrawal' => 'Retraits',
                    ]),
            ])
            // --- EMBELLISSEMENT SI VIDE ---
            ->emptyStateHeading('Aucune transaction pour le moment')
            ->emptyStateDescription('Dès que vous rechargerez votre compte ou ferez un achat, l\'historique apparaîtra ici.')
            ->emptyStateIcon('heroicon-o-banknotes')
            ->emptyStateActions([
                // Un bouton au milieu de l'écran vide pour inciter à recharger
                Action::make('recharge_empty')
                    ->label('Faire mon premier dépôt')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        Select::make('gateway')
                            ->label('Moyen de paiement')
                            ->options([
                                'fedapay' => 'FedaPay (CFA)',
                                // 'stripe' => 'Stripe (International)', // Prêt pour le futur
                            ])
                            ->default('fedapay')
                            ->required(),
                        TextInput::make('amount')
                            ->label('Montant')
                            ->numeric()
                            ->required(),
                        Select::make('currency')
                            ->label('Devise')
                            ->options(['XOF' => 'XOF'])
                            ->default('XOF'),
                    ])
                    // ... dans getHeaderActions ou emptyStateActions
                    ->action(function (array $data, PaymentGatewayManager $gatewayManager) {
                        try {
                            // On récupère l'user connecté et son wallet
                            $user = Auth::user();
                            $wallet = $user->wallet; // Assure-toi que la relation est définie dans ton modèle User

                            if (!$wallet) {
                                throw new \Exception("Vous ne possédez pas encore de portefeuille actif.");
                            }

                            $gateway = $gatewayManager->getGateway($data['gateway']);

                            $depositRequest = new DepositRequest(
                                user: $user, // Plus de $record->user, on utilise $user directement
                                amount: $data['amount'],
                                currency: $data['currency'],
                                metadata: [
                                    'wallet_id' => $wallet->id,
                                    'source' => 'Upesi App'
                                ]
                            );

                            $response = $gateway->initiateDeposit($depositRequest);

                            if ($response->success) {
                                return redirect()->away($response->paymentUrl);
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
            ]);
    }
}
