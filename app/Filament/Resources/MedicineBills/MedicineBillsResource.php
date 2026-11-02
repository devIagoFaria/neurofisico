<?php

namespace App\Filament\Resources\MedicineBills;

use App\Enums\NavigationSort;
use BackedEnum;
use Filament\Tables\Table;
use App\Models\MedicineBill;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Filament\Clusters\Medicines\MedicinesCluster;
use App\Filament\Resources\MedicineBills\Pages\EditMedicineBills;
use App\Filament\Resources\MedicineBills\Pages\ListMedicineBills;
use App\Filament\Resources\MedicineBills\Pages\CreateMedicineBills;
use App\Filament\Resources\MedicineBills\Pages\ViewMedicineBill;
use App\Filament\Resources\MedicineBills\Schemas\MedicineBillsForm;
use App\Filament\Resources\MedicineBills\Tables\MedicineBillsTable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class MedicineBillsResource extends Resource
{
    protected static ?string $model = MedicineBill::class;

    public static function canViewAny(): bool
    {
        return checkPermission('manage_medicines');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Receipt;
    protected static ?string $cluster = MedicinesCluster::class;

    protected static ?string $recordTitleAttribute = 'Medicine Bills';
    protected static ?int $navigationSort = NavigationSort::MEDICINE_BILLS->value;

    public static function getModelLabel(): string
    {
        return __('messages.medicine_bills.medicine_bill');
    }

    public static function form(Schema $schema): Schema
    {
        return MedicineBillsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicineBillsTable::configure($table);
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
            'index' => ListMedicineBills::route('/'),
            'create' => CreateMedicineBills::route('/create'),
            'edit' => EditMedicineBills::route('/{record}/edit'),
            'view' => ViewMedicineBill::route('/{record}'),
        ];
    }
}
