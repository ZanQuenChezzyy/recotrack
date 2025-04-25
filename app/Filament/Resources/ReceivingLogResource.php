<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReceivingLogResource\Pages;
use App\Filament\Resources\ReceivingLogResource\RelationManagers;
use App\Models\ReceivingLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReceivingLogResource extends Resource
{
    protected static ?string $model = ReceivingLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('purchase_order_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('uom_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('stage')
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('monitoring_date')
                    ->required(),
                Forms\Components\DatePicker::make('qc_date')
                    ->required(),
                Forms\Components\DatePicker::make('received_date'),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('purchase_order_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('uom_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stage')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('monitoring_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('qc_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('received_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageReceivingLogs::route('/'),
        ];
    }
}
