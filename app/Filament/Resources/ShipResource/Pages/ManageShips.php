<?php

namespace App\Filament\Resources\ShipResource\Pages;

use App\Filament\Resources\ShipResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageShips extends ManageRecords
{
    protected static string $resource = ShipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
