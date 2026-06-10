<?php

namespace App\Filament\Resources\PushSubscriptions;

use App\Filament\Resources\PushSubscriptions\Pages\ManagePushSubscriptions;
use App\Models\PushSubscription;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PushSubscriptionResource extends Resource
{
    protected static ?string $model = PushSubscription::class;

    protected static string | UnitEnum | null $navigationGroup = 'Notifications';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::DevicePhoneMobile;
    protected static ?string $navigationLabel = 'Abonnements push';
    protected static ?string $modelLabel = 'Abonnement push';
    protected static ?string $pluralModelLabel = 'Abonnements push';
    protected static ?string $recordTitleAttribute = 'token';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Utilisateur')
                    ->relationship('user', 'id')
                    ->preload()
                    ->required(),
                Textarea::make('token')
                    ->label('Token')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('platform')
                    ->label('Plateforme')
                    ->required(),
                TextInput::make('device_name')
                    ->label('Appareil')
                    ->default(null),
                Toggle::make('is_active')
                    ->label('Actif')
                    ->required(),
                DateTimePicker::make('last_used_at')
                    ->label('Dernière utilisation'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('user.id')
                    ->label('Utilisateur'),
                TextEntry::make('token')
                    ->label('Token')
                    ->columnSpanFull(),
                TextEntry::make('platform')
                    ->label('Plateforme'),
                TextEntry::make('device_name')
                    ->label('Appareil')
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                TextEntry::make('last_used_at')
                    ->label('Dernière utilisation')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('token')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('user.id')
                    ->label('Utilisateur')
                    ->searchable(),
                TextColumn::make('platform')
                    ->label('Plateforme')
                    ->searchable(),
                TextColumn::make('device_name')
                    ->label('Appareil')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                TextColumn::make('last_used_at')
                    ->label('Dernière utilisation')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePushSubscriptions::route('/'),
        ];
    }
}
