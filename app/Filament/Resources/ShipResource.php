<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\Reference;
use App\Filament\Resources\ShipResource\Pages;
use App\Filament\Resources\ShipResource\RelationManagers;
use App\Models\Ship;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShipResource extends Resource
{
    protected static ?string $model = Ship::class;
    protected static ?string $cluster = Reference::class;
    protected static ?string $label = 'Kapal';
    protected static ?string $navigationIcon = 'heroicon-o-lifebuoy';
    protected static ?string $activeNavigationIcon = 'heroicon-s-lifebuoy';
    protected static ?int $navigationSort = 4;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() < 2 ? 'danger' : 'info';
    }
    protected static ?string $navigationBadgeTooltip = 'Total Kapal';
    protected static ?string $slug = 'kapal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Kapal')
                    ->placeholder('Masukkan Nama Kapal')
                    ->helperText('Contoh: MV Ning Feng 316, dan Lainnya...')
                    ->inlineLabel()
                    ->minLength(5)
                    ->maxLength(45)
                    ->columnSpanFull()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
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
            'index' => Pages\ManageShips::route('/'),
        ];
    }
}
