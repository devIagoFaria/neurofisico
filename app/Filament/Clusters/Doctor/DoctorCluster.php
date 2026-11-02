<?php

namespace App\Filament\Clusters\Doctor;

use BackedEnum;
use App\Enums\NavigationSort;
use Filament\Clusters\Cluster;
use ToneGabes\Filament\Icons\Enums\Phosphor;
use Filament\Pages\Enums\SubNavigationPosition;

class DoctorCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Phosphor::Stethoscope;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = NavigationSort::DOCTOR_CLUSTER->value;

    public static function getNavigationLabel(): string
    {
        return __('messages.doctors');
    }
}
