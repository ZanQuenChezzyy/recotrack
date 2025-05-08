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
use Filament\Forms\Set;
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
                Group::make([
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
                                            ->editOptionForm([
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
                                            ])
                                            ->editOptionForm([
                                                TextInput::make('name')
                                                    ->label('Nama')
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
                                            ->editOptionForm([
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
                                ->editOptionForm([
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
                                            ->editOptionForm([
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
                                            ])
                                            ->editOptionForm([
                                                TextInput::make('name')
                                                    ->label('Nama')
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
                                            ->editOptionForm([
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
                                    $uomIdToSet = null;

                                    if ($state) {
                                        $purchaseOrder = PurchaseOrder::with('materials')->find($state);

                                        if ($purchaseOrder && $purchaseOrder->materials) {
                                            $materialTypeId = $purchaseOrder->materials->type_id;

                                            // Cari UOM pertama yang punya type_id yang sama
                                            $matchingUom = Uom::where('type_id', $materialTypeId)->first();
                                            if ($matchingUom) {
                                                $uomIdToSet = $matchingUom->id;
                                            }

                                            // Set nilai default field lain jika diperlukan
                                            $set('vendor', $purchaseOrder->vendors?->name ?? '');
                                            $set('ship', $purchaseOrder->ships?->name ?? '');
                                            $set('material', $purchaseOrder->materials->name ?? '');
                                        } else {
                                            // Clear jika tidak ada PO
                                            $set('vendor', '');
                                            $set('ship', '');
                                            $set('material', '');
                                        }
                                    } else {
                                        $set('vendor', '');
                                        $set('ship', '');
                                        $set('material', '');
                                    }

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
                                            $vendorName = Cache::remember("po_{$selectedPoId}_vendor_name", now()->addMinutes(2), function () use ($selectedPoId) {
                                                $po = PurchaseOrder::find($selectedPoId);
                                                return $po?->vendors?->name;
                                            });

                                            return $vendorName ?? 'Tidak Ada Data Vendor (dari PO terpilih)';

                                        } elseif ($record?->purchase_order_id) {
                                            return $record->purchaseOrder?->vendors?->name ?? 'Tidak Ada Data Vendor (dari record awal)';
                                        } else {
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
                    Section::make()
                        ->schema([
                            Placeholder::make('created_at')
                                ->label('Dibuat Saat')
                                ->content(fn(ReceivingLog $record): ?string => $record->created_at?->diffForHumans()),

                            Placeholder::make('updated_at')
                                ->label('Terakhir Diperbarui')
                                ->content(fn(ReceivingLog $record): ?string => $record->updated_at?->diffForHumans()),

                            Placeholder::make('created_by')
                                ->label('Dibuat Oleh')
                                ->content(fn(ReceivingLog $record) => $record->createdBy?->name ?? '-'),

                            Placeholder::make('updated_by')
                                ->label('Diperbarui Oleh')
                                ->content(fn(ReceivingLog $record) => $record->updatedBy?->name ?? '-'),
                        ])
                        ->columns(2)
                        ->columnSpan(1)
                        ->hidden(fn(?ReceivingLog $record) => $record === null),
                ]),
                Section::make('Data Monitoring')
                    ->schema([
                        Forms\Components\Select::make('uom_id')
                            ->label('UOM')
                            ->placeholder('Pilih UOM')
                            ->relationship(
                                'uoms', // relasi ke model Uom (di ReceivingLog)
                                'code', // field yang ditampilkan di dropdown
                                fn($query, Forms\Get $get) => $query->when(
                                    $get('purchase_order_id'),
                                    function ($query, $poId) {
                                        $purchaseOrder = PurchaseOrder::with('materials')->find($poId);
                                        if ($purchaseOrder && $purchaseOrder->materials && $purchaseOrder->materials->type_id) {
                                            $query->where('type_id', $purchaseOrder->materials->type_id);
                                        }
                                    }
                                )
                            )
                            ->disabled(fn(Forms\Get $get): bool => !$get('purchase_order_id'))
                            ->native(false)
                            ->preload()
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('quantity', '1'))
                            ->createOptionForm([
                                Group::make([
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
                                        ->autosize()
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ])->columns(2),
                            ])
                            ->editOptionForm([
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
                            ]),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Kuantitas')
                            ->placeholder('Masukkan Kuantitas')
                            ->prefix('Kuantitas')
                            ->suffix(function (Get $get): ?string {
                                $uomId = $get('uom_id');
                                if ($uomId) {
                                    $uom = Uom::find($uomId);
                                    return $uom?->code;
                                }
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
                            ->default(1)
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
                        Forms\Components\DatePicker::make('received_date')
                            ->label('Tanggal Diterima (DO)')
                            ->placeholder('Pilih Tanggal Diterima')
                            ->native(false),
                        Forms\Components\DatePicker::make('qc_date')
                            ->label('Tanggal QC')
                            ->placeholder('Pilih Tanggal QC')
                            ->native(false),
                        Group::make([
                            Forms\Components\Textarea::make('notes')
                                ->label('Catatan')
                                ->placeholder('Masukkan Catatan')
                                ->autosize()
                                ->rows(4)
                                ->columnSpan(1),
                        ]),
                        Repeater::make('statusHistories')
                            ->label('Riwayat Status')
                            ->relationship()
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
                                    ->native(false)
                                    ->default(now())
                                    ->required()
                                    ->columnSpan(4),
                            ])
                            ->columns(7)
                            ->columnSpan(2),
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
                    ->date('d/m/Y')
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
                Tables\Columns\TextColumn::make('received_date')
                    ->label('Tanggal DO')
                    ->placeholder('Belum DIterima')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('qc_date')
                    ->label('Tanggal QC')
                    ->placeholder('Belum QC')
                    ->date('d/m/Y')
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

                            $date = $status->status_date?->translatedFormat('d/m/Y') ?? '';

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
