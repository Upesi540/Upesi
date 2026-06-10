<?php

namespace App\Filament\App\Pages;

use App\Models\KycVerification;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use UnitEnum;

class SubmitKyc extends Page implements HasSchemas, HasActions
{
    use InteractsWithSchemas;
    use InteractsWithActions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;
    protected string $view = 'filament.app.pages.submit-kyc';
    protected static ?string $navigationLabel = 'Ma Vérification KYC';
    protected static ?string $title = 'Vérification d\'Identité';
    protected static string|UnitEnum|null $navigationGroup = 'Compte & conformité kyc';

    public ?array $data = [];
    public ?KycVerification $kyc = null;

    public function mount(): void
    {
        $this->kyc = Auth::user()->kyc;

        if ($this->kyc) {
            $this->form->fill($this->kyc->toArray());
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // --- SECTION STATUT ---
                Section::make('Statut de votre dossier')
                    ->description(fn() => $this->kyc?->admin_notes ? "Note de l'admin : " . $this->kyc->admin_notes : null)
                    ->schema([
                        Placeholder::make('status_display')
                            ->label('État de la vérification')
                            ->content(function () {
                                $status = $this->kyc?->status;
                                [$label, $color, $icon] = match ($status) {
                                    'approved' => ['Dossier Validé', '#22c55e', '✅'],
                                    'rejected' => ['Dossier Rejeté', '#ef4444', '❌'],
                                    'pending'  => ['En cours de révision', '#f59e0b', '⏳'],
                                    default    => ['Non soumis', '#6b7280', '⚪'],
                                };

                                return new HtmlString("
                                    <div style='display: flex; align-items: center; gap: 8px;'>
                                        <span style='color: {$color}; font-size: 1.2rem;'>{$icon}</span>
                                        <span style='background-color: {$color}22; color: {$color}; padding: 4px 12px; border-radius: 9999px; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; border: 1px solid {$color}44;'>
                                            {$label}
                                        </span>
                                    </div>
                                ");
                            }),
                    ]),

                // --- SECTION IDENTITÉ (OBLIGATOIRE POUR TOUS) ---
                Section::make('Identité du Propriétaire')
                    ->description('Ces informations sont obligatoires pour tous les membres.')
                    ->schema([
                        Select::make('entity_type')
                            ->label('Vous êtes :')
                            ->options([
                                'individual' => 'Un Particulier / Planteur',
                                'business' => 'Une Entreprise / Boutique',
                            ])
                            ->required()
                            ->live(),

                        Select::make('document_type')
                            ->label('Type de pièce d\'identité')
                            ->options([
                                'cni' => 'Carte Nationale d\'Identité',
                                'passport' => 'Passeport',
                                'planter_card' => 'Carte de Planteur',
                            ])
                            ->required()
                            ->live(),

                        TextInput::make('document_number')
                            ->label('Numéro de la pièce')
                            ->required()
                            ->live(onBlur: true),

                        DatePicker::make('expiry_date')
                            ->label('Date d\'expiration de la pièce')
                            ->required(),

                        FileUpload::make('document_files')
                            ->label('Photos du document (Recto / Verso)')
                            ->multiple()
                            ->maxFiles(2)
                            ->imageEditor()
                            ->image()
                            ->directory('kyc-private')
                            ->visibility('private')
                            ->required(fn() => blank($this->kyc?->document_files)),

                        FileUpload::make('selfie_path')
                            ->label('Selfie avec votre document')
                            ->image()
                            ->imageEditor()
                            ->directory('kyc-private')
                            ->visibility('private')
                            ->required(fn() => blank($this->kyc?->selfie_path)),
                    ])->columns(2),

                // --- SECTION BUSINESS (OPTIONNELLE) ---
                Section::make('Documents Entreprise (Si applicable)')
                    ->description('Remplissez cette section uniquement si vous possédez ces documents (Marchands pro).')
                    ->collapsed() // Masqué par défaut pour ne pas effrayer les petits planteurs
                    ->schema([
                        TextInput::make('rccm_number')
                            ->label('Numéro RCCM')
                            ->placeholder('Ex: GA-LBV-2026-B-XXXX'),

                        FileUpload::make('rccm_path')
                            ->label('Fichier RCCM')
                            ->directory('kyc-business')
                            ->visibility('private'),

                        TextInput::make('nif_number')
                            ->label('Numéro NIF (Fiscal)')
                            ->placeholder('Ex: 799123 X'),

                        FileUpload::make('cfe_card_path')
                            ->label('Carte CFE (ANPI)')
                            ->directory('kyc-business')
                            ->imageEditor()
                            ->visibility('private'),

                        FileUpload::make('quitus_fiscal_path')
                            ->label('Quitus Fiscal')
                            ->imageEditor()
                            ->directory('kyc-business')
                            ->visibility('private'),
                    ])->columns(2),

                // --- SECTION MAGASIN & LOCALISATION ---
                Section::make('Localisation du Magasin / Activité')
                    ->description('Aidez les clients à vous situer.')
                    ->schema([
                        Textarea::make('store_description')
                            ->label('Description du lieu / Adresse')
                            ->placeholder('Ex: Derrière le grand marché, à côté de la pharmacie...')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('google_maps_url')
                            ->label('Lien Google Maps (Optionnel)')
                            ->url()
                            ->placeholder('https://goo.gl/maps/...'),

                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->placeholder('0.3912'),

                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->placeholder('9.4532'),
                    ])->columns(3),
            ])
            ->statePath('data')
            ->disabled(fn() => $this->kyc?->status === 'approved' && !$this->kyc?->isExpired());
    }

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Action::make('save')
    //             ->label('Soumettre mon dossier')
    //             ->submit('save')
    //             ->visible(fn() => $this->kyc?->status !== 'approved' || $this->kyc?->isExpired()),
    //     ];
    // }
    // Ajoute cette méthode dans ta classe SubmitKyc
    public function submitKycAction(): Action
    {
        return Action::make('submitKyc')
            ->label('Soumettre mon dossier Upesi')
            ->icon('heroicon-m-check-badge')
            ->size('lg')
            // La confirmation magique :
            ->requiresConfirmation()
            ->modalHeading('Confirmer la soumission ?')
            ->modalDescription('Attention : Toute modification de vos documents entraînera une nouvelle vérification par notre équipe. Votre compte pourrait être temporairement suspendu pendant cette période.')
            ->modalSubmitActionLabel('Oui, soumettre')
            ->modalIcon('heroicon-o-exclamation-triangle')
            ->modalIconColor('warning')
            // Si l'utilisateur confirme, on exécute le save
            ->action(fn() => $this->save());
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            $this->kyc = KycVerification::updateOrCreate(
                ['user_id' => Auth::user()->id],
                [
                    'entity_type'        => $data['entity_type'],
                    'document_type'      => $data['document_type'],
                    'document_number'    => $data['document_number'],
                    'document_files'     => $data['document_files'],
                    'selfie_path'        => $data['selfie_path'],
                    'expiry_date'        => $data['expiry_date'],

                    // Nouveaux champs Business
                    'rccm_number'        => $data['rccm_number'] ?? null,
                    'rccm_path'          => $data['rccm_path'] ?? null,
                    'cfe_card_path'      => $data['cfe_card_path'] ?? null,
                    'quitus_fiscal_path' => $data['quitus_fiscal_path'] ?? null,
                    'nif_number'         => $data['nif_number'] ?? null,

                    // Infos Magasin
                    'store_description'  => $data['store_description'],
                    'latitude'           => $data['latitude'] ?? null,
                    'longitude'          => $data['longitude'] ?? null,
                    'google_maps_url'    => $data['google_maps_url'] ?? null,

                    'status'             => 'pending',
                ]
            );

            $this->form->fill($this->kyc->toArray());

            Notification::make()
                ->success()
                ->title('Dossier soumis avec succès')
                ->body('Votre dossier est en cours de vérification par l\'équipe Upesi.')
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}
