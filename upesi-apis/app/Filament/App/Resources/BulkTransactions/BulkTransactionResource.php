<?php

namespace App\Filament\App\Resources\BulkTransactions;

use App\Filament\App\Resources\BulkTransactions\Pages\CreateBulkTransaction;
use App\Filament\App\Resources\BulkTransactions\Pages\EditBulkTransaction;
use App\Filament\App\Resources\BulkTransactions\Pages\ListBulkTransactions;
use App\Filament\App\Resources\BulkTransactions\Pages\ViewBulkTransaction;
use App\Filament\App\Resources\BulkTransactions\Schemas\BulkTransactionForm;
use App\Filament\App\Resources\BulkTransactions\Schemas\BulkTransactionInfolist;
use App\Filament\App\Resources\BulkTransactions\Tables\BulkTransactionsTable;
use App\Models\BulkTransaction;
use App\Models\MerchantProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class BulkTransactionResource extends Resource
{
    protected static ?string $model = BulkTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Ventes/Achats groupés';

    protected static ?string $pluralLabel = 'Transactions groupées';

    protected static string|UnitEnum|null $navigationGroup = 'Marketplace';

    /**
     * 🔥 Vérifier si l'utilisateur est un trader approuvé (requête directe)
     */
    protected static function isActiveTrader(): bool
    {
        return MerchantProfile::where('user_id', Auth::id())
            ->where('type', 'trader')
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * 🔥 Vérifier si l'utilisateur peut voir cette ressource
     */
    public static function canViewAny(): bool
    {
        return self::isActiveTrader();
    }

    /**
     * 🔥 Vérifier si l'utilisateur peut créer une transaction
     */
    public static function canCreate(): bool
    {
        return self::isActiveTrader();
    }

    public static function getEloquentQuery(): Builder
    {
        if (!self::isActiveTrader()) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->where('trader_id', Auth::id());
    }

    public static function form(Schema $schema): Schema
    {
        return BulkTransactionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BulkTransactionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BulkTransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBulkTransactions::route('/'),
            'create' => CreateBulkTransaction::route('/create'),
            'edit' => EditBulkTransaction::route('/{record}/edit'),
            'view' => ViewBulkTransaction::route('/{record}'),
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
        if (!self::isActiveTrader()) {
            return null;
        }

        $count = BulkTransaction::where('trader_id', Auth::id())
            ->where('status', 'pending')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
