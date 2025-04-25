<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class MaterialSpecification extends Cluster
{
    protected static ?string $navigationGroup = 'Data Receiving';
    protected static ?string $title = 'Spesifikasi Material';
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $activeNavigationIcon = 'heroicon-s-exclamation-triangle';
    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'spesifikasi-material';
}
