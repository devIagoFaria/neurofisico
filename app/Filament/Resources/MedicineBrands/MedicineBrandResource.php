<?php

namespace App\Filament\Resources\MedicineBrands;

use App\Enums\NavigationSort;
use BackedEnum;
use App\Models\Brand;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\MedicineBrand;
use Filament\Resources\Resource;
use App\Filament\Clusters\Medicines\MedicinesCluster;
use App\Filament\Resources\MedicineBrands\Pages\EditMedicineBrand;
use App\Filament\Resources\MedicineBrands\Pages\ViewMedicineBrand;
use App\Filament\Resources\MedicineBrands\Pages\ListMedicineBrands;
use App\Filament\Resources\MedicineBrands\Pages\CreateMedicineBrand;
use App\Filament\Resources\MedicineBrands\Schemas\MedicineBrandForm;
use App\Filament\Resources\MedicineBrands\Tables\MedicineBrandsTable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class MedicineBrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    public static function canViewAny(): bool
    {
        return checkPermission('manage_medicines');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::SquaresFour;
    protected static ?string $cluster = MedicinesCluster::class;

    protected static ?string $recordTitleAttribute = 'Medicine Brands';
    protected static ?int $navigationSort = NavigationSort::MEDICINE_BRANDS->value;

    public static function getModelLabel(): string
    {
        return __('messages.medicine.medicine_brand');
    }

    public static function form(Schema $schema): Schema
    {
        return MedicineBrandForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicineBrandsTable::configure($table);
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
            'index' => ListMedicineBrands::route('/'),
            'create' => CreateMedicineBrand::route('/create'),
            'edit' => EditMedicineBrand::route('/{record}/edit'),
            'view' => ViewMedicineBrand::route('/{record}'),
        ];
    }
}
