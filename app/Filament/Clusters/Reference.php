<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Reference extends Cluster
{
    protected static ?string $navigationGroup = 'Data Receiving';
    protected static ?string $title = 'Kelola Referensi';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static ?string $activeNavigationIcon = 'heroicon-s-clipboard-document';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'referensi';
}
