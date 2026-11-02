<?php

namespace App\Filament\Resources\Medicines;

use App\Enums\NavigationSort;
use BackedEnum;
use App\Models\Medicine;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Clusters\Medicines\MedicinesCluster;
use App\Filament\Resources\Medicines\Pages\EditMedicines;
use App\Filament\Resources\Medicines\Pages\ListMedicines;
use App\Filament\Resources\Medicines\Pages\CreateMedicines;
use App\Filament\Resources\Medicines\Schemas\MedicinesForm;
use App\Filament\Resources\Medicines\Tables\MedicinesTable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class MedicinesResource extends Resource
{
    protected static ?string $model = Medicine::class;

    public static function canViewAny(): bool
    {
        //return checkPermission('manage_medicines');
        return false;
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Pill;
    protected static ?string $cluster = MedicinesCluster::class;

    protected static ?string $recordTitleAttribute = 'Medicines';
    protected static ?int $navigationSort = NavigationSort::MEDICINES->value;

    public static function getModelLabel(): string
    {
        return __('messages.medicine.medicine');
    }

    public static function form(Schema $schema): Schema
    {
        return MedicinesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicinesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('messages.medicine.medicine_details'))
                ->columns(2)
                ->columnSpanFull()
                ->components([
                    TextEntry::make('name')
                        ->label(__('messages.medicine.medicine')),
                    TextEntry::make('brand.name')
                        ->label(__('messages.medicine.brand')),
                    TextEntry::make('category.name')
                        ->label(__('messages.medicine.category')),
                    TextEntry::make('quantity')
                        ->label(__('messages.medicine.quantity')),
                    TextEntry::make('available_quantity')
                        ->label(__('messages.medicine.available_quantity')),
                    TextEntry::make('salt_composition')
                        ->label(__('messages.medicine.salt_composition')),
                    TextEntry::make('selling_price')
                        ->formatStateUsing(fn($record) => getCurrencyIcon($record->currency_id) . number_format($record->selling_price, 2))
                        ->label(__('messages.medicine.selling_price')),
                    TextEntry::make('buying_price')
                        ->formatStateUsing(fn($record) => getCurrencyIcon($record->currency_id) . number_format($record->buying_price, 2))
                        ->label(__('messages.medicine.buying_price')),
                    TextEntry::make('side_effects')
                        ->label(__('messages.medicine.side_effects'))
                        ->default(__('messages.common.n/a')),
                    TextEntry::make('created_at')
                        ->label(__('messages.web.created_at'))
                        ->since(),
                    TextEntry::make('updated_at')
                        ->label(__('messages.patient.last_updated'))
                        ->since(),
                    TextEntry::make('description')
                        ->label(__('messages.medicine.description'))
                        ->default(__('messages.common.n/a')),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedicines::route('/'),
            'create' => CreateMedicines::route('/create'),
            'edit' => EditMedicines::route('/{record}/edit'),
        ];
    }
}
