<?php

namespace App\Filament\Clusters\Services;

use BackedEnum;
use App\Enums\NavigationSort;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use Filament\Pages\Enums\SubNavigationPosition;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ServicesCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Phosphor::UserGear;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected static ?int $navigationSort = NavigationSort::SERVICES->value;
    public static function getNavigationLabel(): string
    {
        return __('messages.services');
    }
}
