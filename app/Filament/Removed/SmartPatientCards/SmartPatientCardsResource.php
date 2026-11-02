<?php

namespace App\Filament\Doctor\Resources\SmartPatientCards;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Enums\NavigationSort;
use Filament\Resources\Resource;
use App\Models\SmartPatientCards;
use Filament\Support\Icons\Heroicon;
use App\Filament\Doctor\Clusters\SmartPatientCard\SmartPatientCardCluster;
use App\Filament\Doctor\Resources\SmartPatientCards\Pages\EditSmartPatientCards;
use App\Filament\Doctor\Resources\SmartPatientCards\Pages\ListSmartPatientCards;
use App\Filament\Doctor\Resources\SmartPatientCards\Pages\CreateSmartPatientCards;
use App\Filament\Doctor\Resources\SmartPatientCards\Schemas\SmartPatientCardsForm;
use App\Filament\Doctor\Resources\SmartPatientCards\Tables\SmartPatientCardsTable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class SmartPatientCardsResource extends Resource
{
    protected static ?string $model = SmartPatientCards::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::IdentificationCard;
    
    protected static ?string $cluster = SmartPatientCardCluster::class;

    protected static ?string $recordTitleAttribute = 'Smart Patient Cards';
    protected static ?int $navigationSort = NavigationSort::SMART_PATIENT_CARDS->value;

    public static function getModelLabel(): string
    {
        return __('messages.smart_patient_card.smart_patient_cards');
    }

    public static function form(Schema $schema): Schema
    {
        return SmartPatientCardsForm::configure($schema);
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
            'create' => CreateSmartPatientCards::route('/create'),
            'edit' => EditSmartPatientCards::route('/{record}/edit'),
        ];
    }
}
