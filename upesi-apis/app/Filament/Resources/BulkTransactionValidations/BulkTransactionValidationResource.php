<?php

namespace App\Filament\Resources\BulkTransactionValidations;

use App\Filament\Resources\BulkTransactionValidations\Pages\ListBulkTransactionValidations;
use App\Filament\Resources\BulkTransactionValidations\Pages\ViewBulkTransactionValidation;
use App\Filament\Resources\BulkTransactionValidations\Schemas\BulkTransactionValidationForm;
use App\Filament\Resources\BulkTransactionValidations\Schemas\BulkTransactionValidationInfolist;
use App\Filament\Resources\BulkTransactionValidations\Tables\BulkTransactionValidationsTable;
use App\Models\BulkTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class BulkTransactionValidationResource extends Resource
{
    protected static ?string $model = BulkTransaction::class;  // ← BulkTransaction, pas BulkTransactionValidation

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Validations groupées';

    protected static ?string $pluralLabel = 'Transactions à valider';

    protected static string|UnitEnum|null $navigationGroup = 'Modération';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc');
    }

    public static function form(Schema $schema): Schema
    {
        return BulkTransactionValidationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BulkTransactionValidationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BulkTransactionValidationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBulkTransactionValidations::route('/'),
            'view' => ViewBulkTransactionValidation::route('/{record}'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = BulkTransaction::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
