<?php

namespace App\Filament\Clusters\Medicines;

use BackedEnum;
use App\Enums\NavigationSort;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use Filament\Pages\Enums\SubNavigationPosition;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class MedicinesCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Phosphor::Pill;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected static ?int $navigationSort = NavigationSort::MEDICINES->value;
    public static function getNavigationLabel(): string
    {
        return __('messages.medicines');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

}
