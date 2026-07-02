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
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class MyWallet extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationLabel = 'Mon Portefeuille';
    protected static ?string $title = 'Gestion du Portefeuille';
    protected string $view = 'filament.app.pages.my-wallet';
    protected static ?int $navigationSort = 1;

    /**
     * 🔥 Pays supportés par FedaPay
     */
    protected function getFedapayCountries(): array
    {
        return ['tg', 'bj', 'ci', 'sn', 'cm', 'ml', 'ne', 'bf', 'gn', 'gh', 'ng'];
    }

    /**
     * 🔥 Formulaire de recharge réutilisable
     */
    protected function getRechargeSchema(): array
    {
        return [
            Select::make('gateway')
                ->label('Moyen de paiement')
                ->options([
                    'fedapay' => 'FedaPay (CFA)',
                ])
                ->default('fedapay')
                ->required(),

            TextInput::make('amount')
                ->label('Montant (FCFA)')
                ->numeric()
                ->required()
                ->minValue(100)
                ->maxValue(1000000)
                ->prefix('XOF'),

            // ✅ PhoneInput avec les BONNES méthodes (selon la doc)
            PhoneInput::make('phone')
                ->label('Numéro de téléphone')
                ->required()
                ->placeholder('90 12 34 56')
                ->defaultCountry('tg')
                ->initialCountry('tg')
                ->onlyCountries($this->getFedapayCountries())
                ->separateDialCode(true)
                ->formatOnDisplay(true), // ✅ La BONNE méthode
        ];
    }

    /**
     * 🔥 Action de recharge réutilisable
     */
    protected function getRechargeAction(): Action
    {
        return Action::make('recharger_wallet')
            ->label('Recharger')
            ->icon('heroicon-o-credit-card')
            ->schema($this->getRechargeSchema())
            ->action(function (array $data, PaymentGatewayManager $gatewayManager) {
                try {
                    $user = Auth::user();
                    $wallet = $user->wallet;

                    if (!$wallet) {
                        throw new \Exception("Vous ne possédez pas encore de portefeuille actif.");
                    }

                    // ✅ Extraire le numéro et le pays
                    $phoneData = $this->extractPhoneData($data['phone']);

                    $gateway = $gatewayManager->getGateway($data['gateway']);

                    $depositRequest = new DepositRequest(
                        user: $user,
                        amount: $data['amount'],
                        currency: 'XOF',
                        metadata: [
                            'wallet_id' => $wallet->id,
                            'source' => 'Upesi App',
                            'phone' => $data['phone'],
                            'country' => $phoneData['country'],
                        ]
                    );

                    $response = $gateway->initiateDeposit($depositRequest);

                    if ($response->success) {
                        return redirect()->away($response->paymentUrl);
                    }
                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();

                    if (str_contains($errorMessage, 'La création de la transaction a échoué')) {
                        $errorMessage = '❌ Échec de la transaction. Vérifiez votre numéro de téléphone.';
                    }

                    Notification::make()
                        ->title('Erreur')
                        ->body($errorMessage)
                        ->danger()
                        ->send();
                }
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getRechargeAction(),
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
            ->emptyStateHeading('Aucune transaction pour le moment')
            ->emptyStateDescription('Dès que vous rechargerez votre compte ou ferez un achat, l\'historique apparaîtra ici.')
            ->emptyStateIcon('heroicon-o-banknotes')
            ->emptyStateActions([
                $this->getRechargeAction(),
            ]);
    }

    /**
     * Extrait le numéro local et le code pays du PhoneInput
     */
    protected function extractPhoneData($phoneValue): array
    {
        if (is_array($phoneValue)) {
            return [
                'number' => $this->formatPhoneNumber($phoneValue['number'] ?? $phoneValue['phone'] ?? ''),
                'country' => strtoupper($phoneValue['country'] ?? 'TG'),
            ];
        }

        $phone = $phoneValue;
        $countryCode = 'TG';

        $countryPrefixes = [
            '228' => 'TG', '229' => 'BJ', '225' => 'CI',
            '221' => 'SN', '237' => 'CM', '223' => 'ML',
            '227' => 'NE', '226' => 'BF', '224' => 'GN',
            '233' => 'GH', '234' => 'NG',
        ];

        $phone = ltrim($phone, '+');

        foreach ($countryPrefixes as $prefix => $code) {
            if (str_starts_with($phone, $prefix)) {
                $countryCode = $code;
                $phone = substr($phone, strlen($prefix));
                break;
            }
        }

        return [
            'number' => $this->formatPhoneNumber($phone),
            'country' => $countryCode,
        ];
    }

    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (empty($phone)) {
            return '90123456';
        }

        if (strlen($phone) > 8) {
            $phone = substr($phone, -8);
        }

        if (strlen($phone) < 8) {
            $phone = str_pad($phone, 8, '0', STR_PAD_LEFT);
        }

        return $phone;
    }
}
