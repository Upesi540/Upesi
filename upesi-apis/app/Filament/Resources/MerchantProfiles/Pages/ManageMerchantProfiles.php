<?php

namespace App\Filament\Resources\MerchantProfiles\Pages;

use App\Filament\Resources\MerchantProfiles\MerchantProfileResource;
use App\Models\MerchantProfile;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ManageMerchantProfiles extends ListRecords
{
    protected static string $resource = MerchantProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        return [
            'Tous' => Tab::make()
                ->badge(MerchantProfile::count())
                ->icon('heroicon-o-rectangle-stack'),

            'En attente' => Tab::make()
                ->badge(MerchantProfile::where('status', 'pending')->count())
                ->badgeColor('danger')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'pending')),

            'Approuvés' => Tab::make()
                ->badge(MerchantProfile::where('status', 'approved')->count())
                ->badgeColor('success')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'approved')),

            'Rejetés' => Tab::make()
                ->badge(MerchantProfile::where('status', 'rejected')->count())
                ->badgeColor('danger')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'rejected')),
        ];
    }
}
