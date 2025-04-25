<?php

namespace App\Filament\Resources\ReceivingLogResource\Pages;

use App\Filament\Resources\ReceivingLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageReceivingLogs extends ManageRecords
{
    protected static string $resource = ReceivingLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
