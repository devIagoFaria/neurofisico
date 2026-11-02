<?php

namespace App\Filament\Resources\UsedMedicines;

use App\Enums\NavigationSort;
use BackedEnum;
use Filament\Tables\Table;
use App\Models\SaleMedicine;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Clusters\Medicines\MedicinesCluster;
use App\Filament\Resources\UsedMedicines\Pages\EditUsedMedicine;
use App\Filament\Resources\UsedMedicines\Pages\ListUsedMedicines;
use App\Filament\Resources\UsedMedicines\Pages\CreateUsedMedicine;
use App\Filament\Resources\UsedMedicines\Schemas\UsedMedicineForm;
use App\Filament\Resources\UsedMedicines\Tables\UsedMedicinesTable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class UsedMedicineResource extends Resource
{
    protected static ?string $model = SaleMedicine::class;

    public static function canViewAny(): bool
    {
        return checkPermission('manage_medicines');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::ChartPieSlice;
    protected static ?string $cluster = MedicinesCluster::class;

    protected static ?string $recordTitleAttribute = 'Used Medicine';
    protected static ?int $navigationSort = NavigationSort::USED_MEDICINES->value;

    public static function getModelLabel(): string
    {
        return __('messages.used_medicine.used_medicine');
    }

    public static function form(Schema $schema): Schema
    {
        return UsedMedicineForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsedMedicinesTable::configure($table);
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
            'index' => ListUsedMedicines::route('/'),
            // 'create' => CreateUsedMedicine::route('/create'),
            // 'edit' => EditUsedMedicine::route('/{record}/edit'),
        ];
    }
}
