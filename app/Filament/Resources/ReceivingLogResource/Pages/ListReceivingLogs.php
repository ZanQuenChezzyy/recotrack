<?php

namespace App\Filament\Resources\ReceivingLogResource\Pages;

use App\Filament\Resources\ReceivingLogResource;
use App\Filament\Widgets\ReceivingLogStats;
use App\Models\Material;
use App\Models\ReceivingLog;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ListReceivingLogs extends ListRecords
{
    protected static string $resource = ReceivingLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        // 1. Mulai dengan Tab 'All'
        $tabs = [
            'all' => Tab::make('Semua')
        ];

        // 2. Dapatkan user yang sedang login
        $user = Auth::user();
        if (!$user) {
            return $tabs; // Kembalikan 'All' jika tidak ada user
        }

        // 3. Dapatkan ID Tipe yang dimiliki oleh user
        try {
            $userTypeIds = $user->types()->pluck('types.id')->toArray();
        } catch (\Exception $e) {
            // Log::error("Error getting user types: " . $e->getMessage());
            return $tabs; // Kembalikan 'All' jika error
        }

        // Jika user tidak punya tipe, tidak perlu tampilkan tab material
        if (empty($userTypeIds)) {
            return $tabs;
        }

        // 4. Dapatkan semua Material yang memiliki type_id yang relevan dengan user
        $relevantMaterials = Material::whereIn('type_id', $userTypeIds)
            ->orderBy('name') // Urutkan berdasarkan nama material
            ->get();

        // Jika tidak ada material yang relevan, kembalikan 'All' saja
        if ($relevantMaterials->isEmpty()) {
            return $tabs;
        }

        // 5. Buat Tab untuk setiap Material yang relevan
        foreach ($relevantMaterials as $material) {
            // Hitung jumlah ReceivingLog untuk material ini
            // Query ini mirip dengan yang ada di modifyQueryUsing, tapi melakukan count()
            $count = ReceivingLog::query()
                ->whereHas('purchaseOrders', function (Builder $poQuery) use ($material) {
                    // Pastikan nama kolom 'material_id' di tabel 'purchase_orders' sudah benar
                    $poQuery->where('material_id', $material->id);
                })
                ->count();

            // Buat slug unik dari nama material + ID untuk menghindari duplikasi jika nama sama
            $slug = Str::slug($material->name) . '-' . $material->id;

            // Tambahkan Tab baru ke array $tabs
            $tabs[$slug] = Tab::make($material->name) // Gunakan nama Material sebagai label Tab
                ->badge($count) // <-- Modifikasi: Tampilkan hasil count sebagai badge
                ->modifyQueryUsing(function (Builder $query) use ($material) {
                    // Filter query utama (ReceivingLog)
                    // Cari ReceivingLog yang memiliki relasi 'purchaseOrders'
                    // dimana 'purchaseOrders' tersebut memiliki 'material_id'
                    // yang sama dengan ID material dari tab ini ($material->id)
    
                    $query->whereHas('purchaseOrders', function (Builder $poQuery) use ($material) {
                        // Pastikan nama kolom 'material_id' di tabel 'purchase_orders' sudah benar
                        $poQuery->where('material_id', $material->id);
                    });

                    // --- CATATAN PENTING ---
                    // Pastikan relasi di model Anda sudah benar:
                    // 1. ReceivingLog -> purchaseOrders(): BelongsTo App\Models\PurchaseOrder::class
                    // 2. PurchaseOrder -> HARUS punya kolom 'material_id'
                });
        }

        // 6. Kembalikan array semua tab ('All' + tab per material)
        return $tabs;
    }
}
