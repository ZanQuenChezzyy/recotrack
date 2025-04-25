<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\Reference;
use App\Filament\Resources\MaterialResource\Pages;
use App\Filament\Resources\MaterialResource\RelationManagers;
use App\Models\Material;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;
    protected static ?string $cluster = Reference::class;
    protected static ?string $label = 'Material';
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $activeNavigationIcon = 'heroicon-s-archive-box';
    protected static ?int $navigationSort = 1;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() < 2 ? 'danger' : 'info';
    }
    protected static ?string $navigationBadgeTooltip = 'Total Material';
    protected static ?string $slug = 'material';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type_id')
                    ->label('Tipe Material')
                    ->relationship('types', 'name')
                    ->native(false)
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Material')
                    ->placeholder('Masukkan Nama Material')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('types.name')
                    ->label('Tipe')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
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
            'index' => Pages\ManageMaterials::route('/'),
        ];
    }
}
