<?php

namespace App\Filament\Resources\MarketNews;

use App\Filament\Resources\MarketNews\Pages\CreateMarketNews;
use App\Filament\Resources\MarketNews\Pages\EditMarketNews;
use App\Filament\Resources\MarketNews\Pages\ListMarketNews;
use App\Filament\Resources\MarketNews\Schemas\MarketNewsForm;
use App\Filament\Resources\MarketNews\Tables\MarketNewsTable;
use App\Models\MarketNews;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class MarketNewsResource extends Resource
{
    protected static ?string $model = MarketNews::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static ?string $recordTitleAttribute = 'title';

    // Utilisation d'une icône de type "Journal" plus parlante

    // Labels en français pour la navigation et l'interface
    protected static ?string $navigationLabel = 'Actualités';
    protected static ?string $modelLabel = 'Actualité';
    protected static ?string $pluralModelLabel = 'Actualités du Marché';
    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    public static function form(Schema $schema): Schema
    {
        return MarketNewsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarketNewsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarketNews::route('/'),
            'create' => CreateMarketNews::route('/create'),
            'edit' => EditMarketNews::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
