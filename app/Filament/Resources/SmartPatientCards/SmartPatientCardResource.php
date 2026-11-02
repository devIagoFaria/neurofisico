<?php

namespace App\Filament\Resources\SmartPatientCards;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Enums\NavigationSort;
use App\Models\SmartPatientCard;
use Filament\Resources\Resource;
use App\Models\SmartPatientCards;
use Filament\Support\Icons\Heroicon;
use ToneGabes\Filament\Icons\Enums\Phosphor;
use App\Filament\Clusters\SmartPatientCard\SmartPatientCardCluster;
use App\Filament\Resources\SmartPatientCards\Pages\EditSmartPatientCard;
use App\Filament\Resources\SmartPatientCards\Pages\ListSmartPatientCards;
use App\Filament\Resources\SmartPatientCards\Pages\CreateSmartPatientCard;
use App\Filament\Resources\SmartPatientCards\Schemas\SmartPatientCardForm;
use App\Filament\Resources\SmartPatientCards\Tables\SmartPatientCardsTable;

class SmartPatientCardResource extends Resource
{
    protected static ?string $model = SmartPatientCards::class;

    public static function canViewAny(): bool
    {
        //return auth()->user()->hasAnyRole(['clinic_admin', 'doctor']);
        return false;
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::IdentificationCard;

    protected static ?string $cluster = SmartPatientCardCluster::class;

    protected static ?int $navigationSort = NavigationSort::SMART_PATIENT_CARDS->value;

    public static function getModelLabel(): string
    {
        return __('messages.smart_patient_card.smart_patient_cards');
    }

    public static function form(Schema $schema): Schema
    {
        return SmartPatientCardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SmartPatientCardsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSmartPatientCards::route('/'),
            'create' => CreateSmartPatientCard::route('/create'),
            'edit' => EditSmartPatientCard::route('/{record}/edit'),
        ];
    }
}
