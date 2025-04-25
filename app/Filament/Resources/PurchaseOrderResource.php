<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;
    protected static ?string $label = 'Monitoring';
    protected static ?string $navigationGroup = 'Data Monitoring';
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $activeNavigationIcon = 'heroicon-s-presentation-chart-line';
    protected static ?int $navigationSort = 1;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() < 2 ? 'danger' : 'info';
    }
    protected static ?string $navigationBadgeTooltip = 'Total Data Monitoring';
    protected static ?string $slug = 'monitoring';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Purchase Order')
                    ->description('Detail Purchase Order')
                    ->schema([
                        Forms\Components\TextInput::make('number')
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
                    ])->columns(2)
                    ->columnSpan(3),
            ])->columns(7);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vendor_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ship_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('material_id')
                    ->numeric()
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
