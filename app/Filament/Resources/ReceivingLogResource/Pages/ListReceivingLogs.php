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
            $count = ReceivingLog::whereHas('purchaseOrders', function (Builder $poQuery) use ($material) {
                $poQuery->where('material_id', $material->id);
            })
                // Tambahkan filter: hanya hitung ReceivingLog yang TIDAK memiliki statusHistory is_done = true
                ->whereDoesntHave('statusHistories', function (Builder $statusQuery) {
                    $statusQuery->where('is_done', true);
                })
                ->count();

            $slug = Str::slug($material->name) . '-' . $material->id;

            $tabs[$slug] = Tab::make($material->name)
                ->badge($count)
                ->modifyQueryUsing(function (Builder $query) use ($material) {
                    $query->whereHas('purchaseOrders', function (Builder $poQuery) use ($material) {
                        $poQuery->where('material_id', $material->id);
                    })
                        ->whereDoesntHave('statusHistories', function (Builder $statusQuery) {
                            $statusQuery->where('is_done', true);
                        });
                });
        }

        // 6. Kembalikan array semua tab ('All' + tab per material)
        return $tabs;
    }
}
