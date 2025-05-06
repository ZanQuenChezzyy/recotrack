<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\Reference;
use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
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
                    ->label('Nomor PO')
                    ->placeholder('Masukkan Nomor PO')
                    ->required()
                    ->maxLength(12),
                Forms\Components\Select::make('vendor_id')
                    ->label('Vendor')
                    ->relationship('vendors', 'name')
                    ->native(false)
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Nama')
                            ->placeholder('Masukkan Nama')
                            ->minLength(3)
                            ->maxLength(45)
                            ->required(),
                    ])
                    ->required(),
                Forms\Components\Select::make('ship_id')
                    ->label('Kapal (Opsional)')
                    ->relationship('ships', 'name')
                    ->native(false)
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Nama Kapal')
                            ->placeholder('Masukkan Nama')
                            ->minLength(3)
                            ->maxLength(45)
                            ->required(),
                    ]),
                Forms\Components\Select::make('material_id')
                    ->label('Material')
                    ->relationship('materials', 'name')
                    ->native(false)
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Nama')
                            ->placeholder('Masukkan Nama')
                            ->minLength(3)
                            ->maxLength(45)
                            ->required(),
                        Forms\Components\Select::make('type_id')
                            ->label('Tipe Material')
                            ->placeholder('Pilih Tipe Material')
                            ->relationship('types', 'name')
                            ->native(false)
                            ->preload()
                            ->searchable()
                            ->required(),
                    ])
                    ->required(),
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
