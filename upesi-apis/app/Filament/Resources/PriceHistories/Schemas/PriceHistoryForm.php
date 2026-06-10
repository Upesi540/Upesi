<?php

namespace App\Filament\Resources\PriceHistories\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PriceHistoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('crop_id')
                    ->relationship('crop', 'name')
                    ->required(),
                TextInput::make('min_price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('max_price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('average_price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('volume_quantity')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('unit_id')
                    ->relationship('unit', 'name')
                    ->required(),
                DatePicker::make('recorded_at')
                    ->required(),
            ]);
    }
}
