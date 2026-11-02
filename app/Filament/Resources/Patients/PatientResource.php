<?php

namespace App\Filament\Resources\Patients;

use BackedEnum;
use App\Models\User;
use App\Models\Patient;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Enums\NavigationSort;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use ToneGabes\Filament\Icons\Enums\Phosphor;
use App\Filament\Resources\Patients\Pages\EditPatient;
use App\Filament\Resources\Patients\Pages\ViewPatient;
use App\Filament\Resources\Patients\Pages\ListPatients;
use App\Filament\Resources\Patients\Pages\CreatePatient;
use App\Filament\Resources\Patients\Schemas\PatientForm;
use App\Filament\Resources\Patients\Tables\PatientsTable;
use App\Filament\Resources\Patients\Schemas\PatientInfolist;

class PatientResource extends Resource
{
    protected static ?string $model = User::class;

    public static function canViewAny(): bool
    {
        return checkPermission('manage_patients');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::IdentificationBadge;

    protected static ?int $navigationSort = NavigationSort::PATIENTS->value;

    public static function getModelLabel(): string
    {
        return __('messages.appointment.patient');
    }

    public static function form(Schema $schema): Schema
    {
        return PatientForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PatientInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PatientsTable::configure($table);
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
            'index' => ListPatients::route('/'),
            'create' => CreatePatient::route('/create'),
            'view' => ViewPatient::route('/{record}'),
            'edit' => EditPatient::route('/{record}/edit'),
        ];
    }
}
