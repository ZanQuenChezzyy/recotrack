<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\Reference;
use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;
    protected static ?string $label = 'Purchase Order';
    protected static ?string $navigationGroup = 'Data Receiving';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $activeNavigationIcon = 'heroicon-s-clipboard-document-list';
    protected static ?int $navigationSort = 3;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() < 2 ? 'danger' : 'info';
    }
    protected static ?string $navigationBadgeTooltip = 'Total Purchase Order';
    protected static ?string $slug = 'purchase-order';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('number')
                    ->label('Purchase Order')
                    ->placeholder('Masukkan Purchase Order')
                    ->required()
                    ->maxLength(12),
                Forms\Components\TextInput::make('vendor_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('ship_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('material_id')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Nomor Purchase Order')
                    ->placeholder('Tidak ada nomor Purchase Order')
                    ->searchable(),
                Tables\Columns\TextColumn::make('materials.name')
                    ->badge()
                    ->color('info')
                    ->numeric(),
                Tables\Columns\TextColumn::make('vendors.name')
                    ->label('Vendor')
                    ->placeholder('Tidak ada vendor')
                    ->numeric(),
                Tables\Columns\TextColumn::make('ships.name')
                    ->label('Kapal')
                    ->placeholder('Tidak ada Kapal')
                    ->numeric(),

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
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->icon('heroicon-o-ellipsis-horizontal-circle')
                    ->color('info')
                    ->tooltip('Aksi')
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
            'index' => Pages\ManagePurchaseOrders::route('/'),
        ];
    }
}
