<?php

namespace App\Filament\Doctor\Resources\Patients;

use BackedEnum;
use App\Models\Patient;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Doctor\Resources\Patients\Pages\EditPatient;
use App\Filament\Doctor\Resources\Patients\Pages\ViewPatient;
use App\Filament\Doctor\Resources\Patients\Pages\ListPatients;
use App\Filament\Doctor\Resources\Patients\Pages\CreatePatient;
use App\Filament\Doctor\Resources\Patients\Schemas\PatientForm;
use App\Filament\Doctor\Resources\Patients\Tables\PatientsTable;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Patients';

    public static function form(Schema $schema): Schema
    {
        return PatientForm::configure($schema);
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
            // 'index' => ListPatients::route('/'),
            // 'create' => CreatePatient::route('/create'),
            // 'edit' => EditPatient::route('/{record}/edit'),
            'view' => ViewPatient::route('/{record}'),
        ];
    }
}
