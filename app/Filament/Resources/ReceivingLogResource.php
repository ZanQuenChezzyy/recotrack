<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReceivingLogResource\Pages;
use App\Filament\Resources\ReceivingLogResource\RelationManagers;
use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\ReceivingLog;
use App\Models\StatusHistory;
use App\Models\Type;
use App\Models\Uom;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Components\Tab;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class ReceivingLogResource extends Resource
{
    protected static ?string $model = ReceivingLog::class;
    protected static ?string $label = 'Monitoring';
    protected static ?string $navigationGroup = 'Monitoring';
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static ?string $activeNavigationIcon = 'heroicon-s-presentation-chart-bar';
    protected static ?int $navigationSort = 2;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() < 2 ? 'danger' : 'info';
    }
    protected static ?string $navigationBadgeTooltip = 'Total data monitoring';
    protected static ?string $slug = 'monitoring';

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
                                ])->columns(2)
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $uomIdToSet = null; // Default UOM ID adalah null
                    
                                if ($state) {
                                    $purchaseOrder = PurchaseOrder::with(['vendors', 'ships', 'materials'])->find($state);

                                    if ($purchaseOrder) {
                                        // Set field-field detail PO (vendor, ship, material)
                                        $set('vendor', $purchaseOrder->vendors?->name ?? '');
                                        $set('ship', $purchaseOrder->ships?->name ?? '');
                                        $set('material', $purchaseOrder->materials?->name ?? '');

                                        // --- Logika untuk menentukan dan set UOM ---
                                        $materialTypeId = $purchaseOrder->materials?->type_id;
                                        $requiredUomCode = null;

                                        if ($materialTypeId === 1) {
                                            $requiredUomCode = 'EA';
                                        } elseif ($materialTypeId === 2) {
                                            $requiredUomCode = 'TON';
                                        }
                                        // Tambahkan kondisi lain jika ada tipe material lain
                                        // elseif ($materialTypeId === 3) {
                                        //     $requiredUomCode = 'XYZ';
                                        // }
                    
                                        // Jika kode UOM yang dibutuhkan ketemu, cari ID-nya
                                        if ($requiredUomCode) {
                                            $matchingUom = Uom::where('code', $requiredUomCode)->first(['id']); // Ambil ID saja
                                            if ($matchingUom) {
                                                $uomIdToSet = $matchingUom->id; // Dapatkan ID UOM yang cocok
                                            }
                                        }
                                        // --- Akhir Logika UOM ---
                    
                                    } else {
                                        // PO tidak ditemukan
                                        $set('vendor', '');
                                        $set('ship', '');
                                        $set('material', '');
                                    }
                                } else {
                                    // PO dikosongkan
                                    $set('vendor', '');
                                    $set('ship', '');
                                    $set('material', '');
                                }

                                // Set nilai uom_id di akhir, baik null maupun ID yang ditemukan
                                $set('uom_id', $uomIdToSet);

                            })
                            ->native(false)
                            ->preload()
                            ->searchable()
                            ->required(),
                        Group::make([
                            Placeholder::make('material')
                                ->content(function (?ReceivingLog $record, Get $get): string {
                                    $selectedPoId = $get('purchase_order_id'); // Ambil ID PO yang dipilih di form
                        
                                    if ($selectedPoId) {
                                        // --- Ada PO dipilih di form ---
                                        // Ambil nama material berdasarkan PO ID terpilih.
                                        // Gunakan cache agar tidak query berulang kali jika form refresh.
                                        $materialName = Cache::remember("po_{$selectedPoId}_material_name", now()->addMinutes(2), function () use ($selectedPoId) {
                                            // Anda bisa optimasi query ini jika perlu
                                            $po = PurchaseOrder::find($selectedPoId);
                                            return $po?->materials?->name; // Ambil nama material dari relasi
                                        });

                                        return $materialName ?? 'Tidak Ada Data (dari PO terpilih)';

                                    } elseif ($record?->purchase_order_id) {
                                        // --- Tidak ada PO dipilih di form, TAPI ini mode edit & record punya PO ---
                                        // Tampilkan data awal dari record.
                                        // Pastikan relasi purchaseOrder.materials sudah di-eager load untuk $record.
                                        return $record->purchaseOrder?->materials?->name ?? 'Tidak Ada Data (dari record awal)';

                                    } else {
                                        // --- Mode create atau tidak ada PO sama sekali ---
                                        return 'Tidak Ada Data';
                                    }
                                }),
                            Placeholder::make('vendor')
                                ->label('Vendor')
                                ->content(function (?ReceivingLog $record, Get $get): string {
                                    $selectedPoId = $get('purchase_order_id'); // Ambil ID PO yang dipilih di form
                        
                                    if ($selectedPoId) {
                                        // --- Ada PO dipilih di form ---
                                        // Ambil nama vendor berdasarkan PO ID terpilih (gunakan cache).
                                        $vendorName = Cache::remember("po_{$selectedPoId}_vendor_name", now()->addMinutes(2), function () use ($selectedPoId) {
                                            $po = PurchaseOrder::find($selectedPoId);
                                            // Sesuaikan 'vendors' dengan nama relasi di model PurchaseOrder Anda
                                            return $po?->vendors?->name;
                                        });

                                        return $vendorName ?? 'Tidak Ada Data Vendor (dari PO terpilih)';

                                    } elseif ($record?->purchase_order_id) {
                                        // --- Tidak ada PO dipilih di form, TAPI ini mode edit & record punya PO ---
                                        // Tampilkan data vendor awal dari record.
                                        // Pastikan relasi purchaseOrder.vendors di-eager load untuk $record.
                                        return $record->purchaseOrder?->vendors?->name ?? 'Tidak Ada Data Vendor (dari record awal)';

                                    } else {
                                        // --- Mode create atau tidak ada PO sama sekali ---
                                        return 'Tidak Ada Data Vendor';
                                    }
                                }),
                            Placeholder::make('ship')
                                ->label('Kapal')
                                ->content(function (?ReceivingLog $record, Get $get): string {
                                    $selectedPoId = $get('purchase_order_id'); // Ambil ID PO yang dipilih di form
                        
                                    if ($selectedPoId) {
                                        // --- Ada PO dipilih di form ---
                                        // Ambil nama kapal berdasarkan PO ID terpilih (gunakan cache).
                                        $shipName = Cache::remember("po_{$selectedPoId}_ship_name", now()->addMinutes(2), function () use ($selectedPoId) {
                                            $po = PurchaseOrder::find($selectedPoId);
                                            // Sesuaikan 'ships' dengan nama relasi di model PurchaseOrder Anda
                                            return $po?->ships?->name; // Relasi kapal bisa jadi null
                                        });
                                        // Tampilkan nama kapal atau pesan jika null/tidak ada
                                        return $shipName ?? 'Tidak Ada Data Kapal (dari PO terpilih)';

                                    } elseif ($record?->purchase_order_id) {
                                        // --- Tidak ada PO dipilih di form, TAPI ini mode edit & record punya PO ---
                                        // Tampilkan data kapal awal dari record.
                                        // Pastikan relasi purchaseOrder.ships di-eager load untuk $record.
                                        return $record->purchaseOrder?->ships?->name ?? 'Tidak Ada Data Kapal (dari record awal)';

                                    } else {
                                        // --- Mode create atau tidak ada PO sama sekali ---
                                        return 'Tidak Ada Data Kapal';
                                    }
                                })->columnSpanFull()
                        ])->columns(2)
                            ->hidden(fn(Forms\Get $get) => !$get('purchase_order_id'))
                    ])->columns(1)
                    ->columnSpan(1),
                Section::make('Data Monitoring')
                    ->schema([
                        Forms\Components\Select::make('uom_id')
                            ->label('UOM')
                            ->placeholder('Pilih UOM')
                            ->relationship(
                                name: 'uoms', // Nama relasi di model ReceivingLog
                                titleAttribute: 'code', // Attribute yang ditampilkan sebagai label
                                modifyQueryUsing: function (Builder $query, Forms\Get $get) {
                                    $purchaseOrderId = $get('purchase_order_id');

                                    // Jika PO belum dipilih, jangan tampilkan UOM apapun
                                    if (!$purchaseOrderId) {
                                        return $query->whereRaw('1 = 0'); // Query yang pasti tidak return hasil
                                    }

                                    // Cari Purchase Order beserta Materialnya
                                    // Sebaiknya gunakan eager loading jika memungkinkan di relasi utama model
                                    $purchaseOrder = PurchaseOrder::find($purchaseOrderId);

                                    // Dapatkan type_id dari material terkait PO
                                    $materialTypeId = $purchaseOrder?->material_id ? $purchaseOrder->materials?->type_id : null;

                                    // Terapkan filter berdasarkan material_type_id
                                    if ($materialTypeId === 1) {
                                        // Jika tipe material = 1, cari UOM dengan code 'EA'
                                        return $query->where('code', 'EA');
                                    } elseif ($materialTypeId === 2) {
                                        // Jika tipe material = 2, cari UOM dengan code 'TON'
                                        return $query->where('code', 'TON');
                                    }
                                    // Tambahkan kondisi lain jika ada tipe material lain
                                    // elseif ($materialTypeId === 3) {
                                    //    return $query->where('code', 'XYZ'); // Contoh
                                    // }
                                    else {
                                        // Jika tipe material tidak dikenali atau tidak ada material,
                                        // jangan tampilkan UOM apapun.
                                        return $query->whereRaw('1 = 0');
                                    }
                                }
                            )
                            ->disabled(fn(Forms\Get $get): bool => !$get('purchase_order_id'))
                            ->native(false)
                            ->preload()
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Kuantitas')
                            ->placeholder('Masukkan Kuantitas')
                            ->prefix('Kuantitas')
                            ->suffix(function (Get $get): ?string {
                                $uomId = $get('uom_id'); // Ambil ID UOM yang dipilih
                    
                                if ($uomId) {
                                    // Cari UOM berdasarkan ID.
                                    // Pertimbangkan caching sederhana jika ini sering terpanggil & jadi isu performa.
                                    $uom = Uom::find($uomId);
                                    // Return kode UOM jika ditemukan, jika tidak return null
                                    return $uom?->code;
                                }

                                // Jika tidak ada UOM yang dipilih, jangan tampilkan suffix
                                return null;
                            })
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
                            ->label('Tanggal QC')
                            ->placeholder('Pilih Tanggal QC')
                            ->native(false),
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
                                        '1' => 'COA',
                                        '2' => '101',
                                        '3' => '103',
                                        '4' => '105',
                                        '5' => '122',
                                        '6' => '161',
                                        '7' => '109',
                                        '8' => '501',
                                        '9' => '551',
                                    ])
                                    ->native(false)
                                    ->preload()
                                    ->searchable()
                                    ->default(1)
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
            ->poll('10s')
            ->deferLoading()
            ->columns([
                Tables\Columns\TextColumn::make('monitoring_date')
                    ->label('Moinitoring')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchaseOrders.number')
                    ->label('No PO')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('purchaseOrders.materials.name')
                    ->label('Material')
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Kuantitas')
                    ->suffix(function (ReceivingLog $record): ?string {
                        return ' ' . $record->uoms?->code;
                    })
                    ->numeric(),
                Tables\Columns\TextColumn::make('uoms.code')
                    ->label('UOM')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('stage')
                    ->label('Tahap')
                    ->prefix('Tahap ')
                    ->badge()
                    ->color('warning')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('qc_date')
                    ->label('Tanggal QC')
                    ->placeholder('Belum QC')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('received_date')
                    ->label('Tanggal Diterima')
                    ->placeholder('Belum DIterima')
                    ->date()
                    ->sortable(),
                TextColumn::make('statusHistories')
                    ->label('Riwayat Purchase Order (PO)')
                    ->placeholder('Tidak ada riwayat PO')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->getStateUsing(function ($record) {
                        return $record->statusHistories->map(function ($status) {
                            $label = match ($status->status) {
                                1 => 'COA',
                                2 => '101',
                                3 => '103',
                                4 => '105',
                                5 => '122',
                                6 => '161',
                                7 => '109',
                                8 => '501',
                                9 => '551',
                                default => 'Unknown',
                            };

                            $badge = $status->is_done
                                ? '<span style="--c-50:var(--success-50);--c-400:var(--success-400);--c-600:var(--success-600);display: inline-block;width: 5rem;text-align: center;" class="fi-badge items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30 fi-color-success">Selesai</span>'
                                : '<span style="--c-50:var(--warning-50);--c-400:var(--warning-400);--c-600:var(--warning-600);display: inline-block;width: 5rem;text-align: center;" class="fi-badge items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30 fi-color-warning">Pending</span>';

                            $date = $status->status_date?->translatedFormat('l, d F Y') ?? '';

                            return "{$label} : {$badge} • {$date}";
                        })->toArray(); // Kembalikan array agar bisa ditampilkan sebagai list
                    })
                    ->bulleted()
                    ->html(),
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
                DateRangeFilter::make('monitoring_date')
                    ->label('Tanggal Monitoring')
                    ->placeholder('Pilih Rentan Tanggal'),
                DateRangeFilter::make('qc_date')
                    ->label('Tanggal QC')
                    ->placeholder('Pilih Rentan Tanggal'),
                DateRangeFilter::make('received_date')
                    ->label('Tanggal Diterima')
                    ->placeholder('Pilih Rentan Tanggal'),
            ], layout: FiltersLayout::AboveContent)->filtersFormColumns(3)
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat'),
                    Tables\Actions\EditAction::make()
                        ->label('Ubah')
                        ->color('info'),
                    Tables\Actions\DeleteAction::make()
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
