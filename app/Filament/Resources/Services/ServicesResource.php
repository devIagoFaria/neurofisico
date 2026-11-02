<?php

namespace App\Filament\Resources\Services;

use App\Enums\NavigationSort;
use BackedEnum;
use App\Models\Service;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Filament\Clusters\Services\ServicesCluster;
use App\Filament\Resources\Services\Pages\EditServices;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Filament\Resources\Services\Pages\CreateServices;
use App\Filament\Resources\Services\Schemas\ServicesForm;
use App\Filament\Resources\Services\Tables\ServicesTable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ServicesResource extends Resource
{
    protected static ?string $model = Service::class;

    public static function canViewAny(): bool
    {
        return checkPermission('manage_services');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Lightning;
    protected static ?string $cluster = ServicesCluster::class;

    protected static ?int $navigationSort = NavigationSort::SERVICES->value;

    protected static ?string $recordTitleAttribute = 'Services';

    public static function getModelLabel(): string
    {
        return __('messages.services');
    }

    public static function form(Schema $schema): Schema
    {
        return ServicesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServicesTable::configure($table);
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
            'index' => ListServices::route('/'),
            'create' => CreateServices::route('/create'),
            'edit' => EditServices::route('/{record}/edit'),
        ];
    }
}
