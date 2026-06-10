<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\Delivery;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeliveriesRelationManager extends RelationManager
{
    protected static string $relationship = 'deliveries';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('transporter_profile_id')
                    ->label('Transporteur')
                    ->relationship('transporter', 'shop_name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('tracking_number')
                    ->label('N° de suivi'),
                Forms\Components\Select::make('status')
                    ->options([
                        Delivery::STATUS_PENDING => 'En attente',
                        Delivery::STATUS_PICKED_UP => 'Récupéré',
                        Delivery::STATUS_IN_TRANSIT => 'En transit',
                        Delivery::STATUS_DELIVERED => 'Livré',
                        Delivery::STATUS_FAILED => 'Échec',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('pickup_address')
                    ->label('Adresse de prise en charge')
                    ->required()
                    ->rows(2),
                Forms\Components\Textarea::make('delivery_address')
                    ->label('Adresse de livraison')
                    ->required()
                    ->rows(2),
                Forms\Components\DateTimePicker::make('estimated_pickup_at')
                    ->label('Prise en charge estimée'),
                Forms\Components\DateTimePicker::make('estimated_delivery_at')
                    ->label('Livraison estimée'),
                Forms\Components\DateTimePicker::make('picked_up_at')
                    ->label('Récupéré le'),
                Forms\Components\DateTimePicker::make('delivered_at')
                    ->label('Livré le'),
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->rows(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transporter.shop_name')
                    ->label('Transporteur'),
                Tables\Columns\TextColumn::make('tracking_number')
                    ->label('N° suivi'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => Delivery::STATUS_PENDING,
                        'info'    => Delivery::STATUS_PICKED_UP,
                        'primary' => Delivery::STATUS_IN_TRANSIT,
                        'success' => Delivery::STATUS_DELIVERED,
                        'danger'  => Delivery::STATUS_FAILED,
                    ]),
                Tables\Columns\TextColumn::make('estimated_delivery_at')
                    ->label('Livraison estimée')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('delivered_at')
                    ->label('Livrée le')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Delivery::STATUS_PENDING => 'En attente',
                        Delivery::STATUS_PICKED_UP => 'Récupéré',
                        Delivery::STATUS_IN_TRANSIT => 'En transit',
                        Delivery::STATUS_DELIVERED => 'Livré',
                        Delivery::STATUS_FAILED => 'Échec',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
