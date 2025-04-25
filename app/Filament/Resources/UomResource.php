<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\MaterialSpecification;
use App\Filament\Resources\UomResource\Pages;
use App\Filament\Resources\UomResource\RelationManagers;
use App\Models\Uom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UomResource extends Resource
{
    protected static ?string $model = Uom::class;
    protected static ?string $cluster = MaterialSpecification::class;
    protected static ?string $label = 'UOM';
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $activeNavigationIcon = 'heroicon-s-calculator';
    protected static ?int $navigationSort = 3;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() < 2 ? 'danger' : 'info';
    }
    protected static ?string $navigationBadgeTooltip = 'Total UOM';
    protected static ?string $slug = 'uom';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->placeholder('Masukkan Kode UOM')
                    ->helperText('Contoh: TON, EA, Dan Lainnya...')
                    ->maxLength(10)
                    ->required(),
                Forms\Components\Select::make('type_id')
                    ->label('Tipe')
                    ->placeholder('Pilih Tipe Material')
                    ->relationship('types', 'name')
                    ->native(false)
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->placeholder('Masukkan Deskripsi')
                    ->helperText('Contoh: Kode UOM yang sering digunakan pada bahan baku NPK / Spareparts / Lainnya.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('types.name')
                    ->label('Tipe')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->wrap()
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ManageUoms::route('/'),
        ];
    }
}
