<?php

namespace App\Filament\App\Resources\BulkTransactions\Pages;

use App\Filament\App\Resources\BulkTransactions\BulkTransactionResource;
use App\Models\BulkTransaction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListBulkTransactions extends ListRecords
{
    protected static string $resource = BulkTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle transaction'),
        ];
    }

    public function getTabs(): array
    {
        $userId = Auth::id();

        return [
            'all' => Tab::make('Tous')
                ->badge(BulkTransaction::where('trader_id', $userId)->count())
                ->modifyQueryUsing(fn($query) => $query),

            'sales' => Tab::make('Ventes groupées')
                ->icon('heroicon-m-shopping-cart')
                ->badge(BulkTransaction::where('trader_id', $userId)->where('type', 'sale')->count())
                ->modifyQueryUsing(fn($query) => $query->where('type', 'sale')),

            'purchases' => Tab::make('Achats groupés')
                ->icon('heroicon-m-truck')
                ->badge(BulkTransaction::where('trader_id', $userId)->where('type', 'purchase')->count())
                ->modifyQueryUsing(fn($query) => $query->where('type', 'purchase')),

            'pending' => Tab::make('En attente')
                ->icon('heroicon-m-clock')
                ->badge(BulkTransaction::where('trader_id', $userId)->where('status', 'pending')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'pending')),

            'approved' => Tab::make('Approuvés')
                ->icon('heroicon-m-check-circle')
                ->badge(BulkTransaction::where('trader_id', $userId)->where('status', 'approved')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'approved')),

            'completed' => Tab::make('Terminés')
                ->icon('heroicon-m-flag')
                ->badge(BulkTransaction::where('trader_id', $userId)->where('status', 'completed')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'completed')),

            'rejected' => Tab::make('Rejetés')
                ->icon('heroicon-m-x-circle')
                ->badge(BulkTransaction::where('trader_id', $userId)->where('status', 'rejected')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'rejected')),

            'draft' => Tab::make('Brouillons')
                ->icon('heroicon-m-pencil-square')
                ->badge(BulkTransaction::where('trader_id', $userId)->where('status', 'draft')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'draft')),
        ];
    }
}
