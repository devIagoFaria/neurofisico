<?php

namespace App\Filament\Resources\PurchaseMedicines;

use App\Enums\NavigationSort;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\PurchaseMedicine;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Clusters\Medicines\MedicinesCluster;
use App\Filament\Resources\PurchaseMedicines\Pages\ViewPurchaseMedicine;
use App\Filament\Resources\PurchaseMedicines\Pages\EditPurchaseMedicines;
use App\Filament\Resources\PurchaseMedicines\Pages\ListPurchaseMedicines;
use App\Filament\Resources\PurchaseMedicines\Pages\CreatePurchaseMedicines;
use App\Filament\Resources\PurchaseMedicines\Schemas\PurchaseMedicinesForm;
use App\Filament\Resources\PurchaseMedicines\Tables\PurchaseMedicinesTable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class PurchaseMedicinesResource extends Resource
{
    protected static ?string $model = PurchaseMedicine::class;

    public static function canViewAny(): bool
    {
        return checkPermission('manage_medicines');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::ListChecks;
    protected static ?string $cluster = MedicinesCluster::class;

    protected static ?string $recordTitleAttribute = 'Purchase Medicines';
    protected static ?int $navigationSort = NavigationSort::PURCHASES_MEDICINES->value;
    public static function getModelLabel(): string
    {
        return __('messages.purchase_medicine.purchase_medicine');
    }

    public static function form(Schema $schema): Schema
    {
        return PurchaseMedicinesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseMedicinesTable::configure($table);
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
            'index' => ListPurchaseMedicines::route('/'),
            'create' => CreatePurchaseMedicines::route('/create'),
            // 'edit' => EditPurchaseMedicines::route('/{record}/edit'),
            'view' => ViewPurchaseMedicine::route('/{record}'),
        ];
    }
}
