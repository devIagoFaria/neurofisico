<?php

namespace App\Filament\Clusters\SmartPatientCard;

use BackedEnum;
use App\Enums\NavigationSort;
use Filament\Clusters\Cluster;
use ToneGabes\Filament\Icons\Enums\Phosphor;
use Filament\Pages\Enums\SubNavigationPosition;

class SmartPatientCardCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Phosphor::IdentificationCard;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = NavigationSort::SMART_PATIENT_CARDS->value;

    public static function getNavigationLabel(): string
    {
        return __('messages.smart_patient_card.smart_patient_cards');
    }
}
