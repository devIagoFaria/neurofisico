<?php

namespace App\Filament\Resources\Visits;

use App\Enums\DoctorNavigationSort;
use BackedEnum;
use App\Models\Visit;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Enums\NavigationSort;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\Visits\Pages\ViewVisit;
use App\Filament\Resources\Visits\Pages\EditVisits;
use App\Filament\Resources\Visits\Pages\ListVisits;
use App\Filament\Resources\Visits\Pages\CreateVisits;
use App\Filament\Resources\Visits\Schemas\VisitsForm;
use App\Filament\Resources\Visits\Tables\VisitsTable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class VisitsResource extends Resource
{
    protected static ?string $model = Visit::class;

    public static function canViewAny(): bool
    {
        //return checkPermission('manage_patient_visits');
        return false;
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Bed;
    public static function getNavigationSort(): ?int
    {
        if (auth()->user()->hasRole('doctor') || auth()->user()->hasRole('patient')) {
            return DoctorNavigationSort::VISITS->value;
        }
        return NavigationSort::VISITS->value;
    }

    protected static ?string $recordTitleAttribute = 'Visits';
    public static function getModelLabel(): string
    {
        return __('messages.visit.visit');
    }

    public static function form(Schema $schema): Schema
    {
        return VisitsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisitsTable::configure($table);
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
            'index' => ListVisits::route('/'),
            'create' => CreateVisits::route('/create'),
            'edit' => EditVisits::route('/{record}/edit'),
            'view' => ViewVisit::route('/{record}')
        ];
    }
}
