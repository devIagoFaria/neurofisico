<?php

namespace App\Filament\Clusters\FrontCms;

use BackedEnum;
use App\Enums\NavigationSort;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use Filament\Pages\Enums\SubNavigationPosition;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class FrontCmsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Phosphor::SquaresFour;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = NavigationSort::CMS->value;

    public static function getNavigationLabel(): string
    {
        return __('messages.front_cms');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
