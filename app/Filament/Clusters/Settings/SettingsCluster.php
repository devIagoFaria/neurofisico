<?php

namespace App\Filament\Clusters\Settings;

use BackedEnum;
use App\Enums\NavigationSort;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use Filament\Pages\Enums\SubNavigationPosition;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class SettingsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Phosphor::GearSix;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected static ?int $navigationSort = NavigationSort::SETTINGS->value;
    public static function getNavigationLabel(): string
    {
        return __('messages.settings');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
