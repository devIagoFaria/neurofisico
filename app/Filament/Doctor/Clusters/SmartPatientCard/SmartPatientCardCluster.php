<?php

namespace App\Filament\Doctor\Clusters\SmartPatientCard;

use BackedEnum;
use App\Enums\NavigationSort;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use Filament\Pages\Enums\SubNavigationPosition;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class SmartPatientCardCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Phosphor::IdentificationCard;
    
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = NavigationSort::APPOINTMENTS->value;

    public static function getNavigationLabel(): string
    {
        return __('messages.smart_patient_card.smart_patient_cards');
    }
}
