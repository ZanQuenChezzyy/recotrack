<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\MaterialSpecification;
use App\Filament\Resources\TypeResource\Pages;
use App\Filament\Resources\TypeResource\RelationManagers;
use App\Models\Type;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TypeResource extends Resource
{
    protected static ?string $model = Type::class;
    protected static ?string $cluster = MaterialSpecification::class;
    protected static ?string $label = 'Tipe Material';
    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $activeNavigationIcon = 'heroicon-s-folder';
    protected static ?int $navigationSort = 3;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() < 2 ? 'danger' : 'info';
    }
    protected static ?string $navigationBadgeTooltip = 'Total Tipe Material';
    protected static ?string $slug = 'tipe-material';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Material')
                    ->placeholder('Masukkan Nama Material')
                    ->helperText('Contoh: Chemical, Bahan Baku NPK, dan Lainnya...')
                    ->inlineLabel()
                    ->minLength(3)
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
            'index' => Pages\ManageTypes::route('/'),
        ];
    }
}
