<?php

namespace App\Filament\Resources\NotificationTemplates;

use App\Filament\Resources\NotificationTemplates\Pages\ManageNotificationTemplates;
use App\Models\NotificationTemplate;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class NotificationTemplateResource extends Resource
{
    protected static ?string $model = NotificationTemplate::class;

    protected static string|UnitEnum|null $navigationGroup = 'Notifications';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;
    protected static ?string $navigationLabel = 'Template de notifications';
    protected static ?string $modelLabel = 'Template de notification';
        protected static ?string $pluralModelLabel = 'Templates de notification';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
                TextInput::make('title')
                    ->label('Titre')
                    ->required(),
                Textarea::make('body')
                    ->label('Corps du message')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('icon_url')
                    ->label('URL de l\'icône')
                    ->url()
                    ->default(null),
                FileUpload::make('image_url')
                    ->label('Image')
                    ->image(),
                TextInput::make('action_url')
                    ->label('URL d\'action')
                    ->url()
                    ->default(null),
                TextInput::make('priority')
                    ->label('Priorité')
                    ->required()
                    ->default('normal'),
                Textarea::make('payload')
                    ->label('Données (JSON)')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('slug')->label('Slug'),
                TextEntry::make('title')->label('Titre'),
                TextEntry::make('body')->label('Corps')->columnSpanFull(),
                TextEntry::make('icon_url')->label('Icône')->placeholder('-'),
                ImageEntry::make('image_url')->label('Image')->placeholder('-'),
                TextEntry::make('action_url')->label('Action')->placeholder('-'),
                TextEntry::make('priority')->label('Priorité'),
                TextEntry::make('payload')->label('Payload')->placeholder('-')->columnSpanFull(),
                TextEntry::make('created_at')->label('Créé le')->dateTime()->placeholder('-'),
                TextEntry::make('updated_at')->label('Mis à jour le')->dateTime()->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('id')->label('ID')->searchable(),
                TextColumn::make('slug')->label('Slug')->searchable(),
                TextColumn::make('title')->label('Titre')->searchable(),
                TextColumn::make('icon_url')->label('Icône')->searchable(),
                ImageColumn::make('image_url')->label('Image'),
                TextColumn::make('action_url')->label('Action')->searchable(),
                TextColumn::make('priority')->label('Priorité')->searchable(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => ManageNotificationTemplates::route('/'),
        ];
    }
}
