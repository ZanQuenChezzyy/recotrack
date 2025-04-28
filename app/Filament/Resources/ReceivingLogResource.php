<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReceivingLogResource\Pages;
use App\Filament\Resources\ReceivingLogResource\RelationManagers;
use App\Models\ReceivingLog;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ReceivingLogResource extends Resource
{
    protected static ?string $model = ReceivingLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Data Purchase Order')
                    ->schema([
                        Forms\Components\Select::make('purchase_order_id')
                            ->label('Purchase Order')
                            ->relationship('purchaseOrders', 'number')
                            ->createOptionForm([
                                Group::make([
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
                                        ->label('Kapal')
                                        ->relationship('ships', 'name')
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
                                ])->columns(2)
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $purchaseOrder = \App\Models\PurchaseOrder::find($state);

                                    if ($purchaseOrder) {
                                        $set('vendor', $purchaseOrder->vendors?->name ?? '');
                                        $set('ship', $purchaseOrder->ships?->name ?? '');
                                        $set('material', $purchaseOrder->materials?->name ?? '');
                                    }
                                } else {
                                    $set('vendor', '');
                                    $set('ship', '');
                                    $set('material', '');
                                }
                            })
                            ->native(false)
                            ->preload()
                            ->searchable()
                            ->required(),
                        Group::make([
                            Forms\Components\TextArea::make('vendor')
                                ->label('Vendor')
                                ->placeholder('Pilih PO terlebih Dahulu')
                                ->autosize()
                                ->rows(1)
                                ->inlineLabel()
                                ->dehydrated(false)
                                ->disabled(),
                            Forms\Components\TextArea::make('ship')
                                ->label('Kapal')
                                ->placeholder('Pilih PO terlebih Dahulu')
                                ->dehydrated(false)
                                ->autosize()
                                ->rows(1)
                                ->inlineLabel()
                                ->disabled(),
                            Forms\Components\TextArea::make('material')
                                ->label('Material')
                                ->placeholder('Pilih PO terlebih Dahulu')
                                ->dehydrated(false)
                                ->autosize()
                                ->rows(1)
                                ->inlineLabel()
                                ->disabled(),
                        ])->hidden(fn(Forms\Get $get) => !$get('purchase_order_id'))
                    ])->columns(1)
                    ->columnSpan(1),
                Section::make('Data Monitoring')
                    ->schema([
                        Forms\Components\Select::make('uom_id')
                            ->label('UOM')
                            ->placeholder('Pilih UOM')
                            ->relationship('uoms', 'code')
                            ->native(false)
                            ->preload()
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Kuantitas')
                            ->placeholder('Masukkan Kuantitas')
                            ->prefix('Kuantitas')
                            ->required()
                            ->numeric()
                            ->default(1),
                        Forms\Components\Select::make('stage')
                            ->label('Tahap')
                            ->placeholder('Pilih Tahap')
                            ->options([
                                '1' => 'Tahap 1',
                                '2' => 'Tahap 2',
                                '3' => 'Tahap 3',
                                '4' => 'Tahap 4',
                                '5' => 'Tahap 5',
                                '6' => 'Tahap 6',
                                '7' => 'Tahap 7',
                                '8' => 'Tahap 8',
                                '9' => 'Tahap 9',
                                '10' => 'Tahap 10',
                                '11' => 'Tahap 11',
                            ])
                            ->native(false)
                            ->preload()
                            ->searchable()
                            ->required(),
                        Forms\Components\DatePicker::make('monitoring_date')
                            ->label('Monitoring Date')
                            ->placeholder('Pilih Tanggal Monitoring')
                            ->native(false)
                            ->default(now())
                            ->required(),
                        Forms\Components\DatePicker::make('qc_date')
                            ->label('QC Date')
                            ->placeholder('Pilih Tanggal QC')
                            ->native(false)
                            ->required(),
                        Forms\Components\DatePicker::make('received_date')
                            ->label('Tanggal Diterima')
                            ->placeholder('Pilih Tanggal Diterima')
                            ->native(false),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->placeholder('Masukkan Catatan')
                            ->rows(4)
                            ->columnSpan(1),
                        Repeater::make('statusHistories')
                            ->relationship()
                            ->label('Riwayat Status')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('')
                                    ->placeholder('Pilih')
                                    ->options([
                                        '1' => '101',
                                        '2' => '103',
                                        '3' => '105',
                                        '4' => '122',
                                        '5' => '161',
                                        '6' => '109',
                                        '7' => '501',
                                        '8' => '551',
                                    ])
                                    ->native(false)
                                    ->preload()
                                    ->searchable()
                                    ->default(2)
                                    ->columnSpan(2)
                                    ->required(),
                                Forms\Components\Toggle::make('is_done')
                                    ->label('')
                                    ->inline(false)
                                    ->columnSpan(1)
                                    ->onIcon('heroicon-m-check')
                                    ->offIcon('heroicon-m-x-mark')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->required(),
                                Forms\Components\DatePicker::make('status_date')
                                    ->label('')
                                    ->placeholder('Pilih Tanggal')
                                    ->columnSpan(4)
                                    ->native(false)
                                    ->required(),
                            ])->columns(7)
                            ->columnSpan(2)
                    ])->columns(3)
                    ->columnSpan(2),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('purchaseOrders.number')
            ->columns([
                Tables\Columns\TextColumn::make('monitoring_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchaseOrders.materials.name')
                    ->label('Material')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('uoms.code')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stage')
                    ->numeric()
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
            'index' => Pages\ListReceivingLogs::route('/'),
            'create' => Pages\CreateReceivingLog::route('/create'),
            'view' => Pages\ViewReceivingLog::route('/{record}'),
            'edit' => Pages\EditReceivingLog::route('/{record}/edit'),
        ];
    }
}
