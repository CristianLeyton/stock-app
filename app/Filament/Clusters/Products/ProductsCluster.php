<?php

namespace App\Filament\Clusters\Products;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use Filament\Pages\Enums\SubNavigationPosition;


use UnitEnum;

class ProductsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $clusterBreadcrumb = 'Productos';
    protected static ?string $navigationLabel = 'Productos';
    protected static bool $hasTitleCaseModelLabel = false;
    protected static ?int $navigationSort = 1;
}
