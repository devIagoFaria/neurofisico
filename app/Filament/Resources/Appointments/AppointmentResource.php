<?php

namespace App\Filament\Resources\Appointments;

use App\Enums\DoctorNavigationSort;
use BackedEnum;
use Filament\Tables\Table;
use App\Models\Appointment;
use Filament\Schemas\Schema;
use App\Enums\NavigationSort;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use ToneGabes\Filament\Icons\Enums\Phosphor;
use App\Filament\Resources\Appointments\Pages\EditAppointment;
use App\Filament\Resources\Appointments\Pages\ViewAppointment;
use App\Filament\Resources\Appointments\Pages\ListAppointments;
use App\Filament\Resources\Appointments\Pages\CreateAppointment;
use App\Filament\Resources\Appointments\Schemas\AppointmentForm;
use App\Filament\Resources\Appointments\Tables\AppointmentsTable;
use App\Filament\Resources\Appointments\Pages\EditAppointmentsPrescription;
use App\Filament\Resources\Appointments\Pages\ViewAppointmentsPrescription;
use App\Filament\Resources\Appointments\Pages\CreateAppointmentsPrescription;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    public static function canViewAny(): bool
    {
        return checkPermission('manage_appointments');
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::CalendarDots;

    public static function getModelLabel(): string
    {
        return __('messages.appointments');
    }
    public static function getNavigationSort(): ?int
    {
        if (auth()->user()->hasRole('doctor') || auth()->user()->hasRole('patient')) {
            return DoctorNavigationSort::APPOINTMENTS->value;
        }
        return NavigationSort::APPOINTMENTS->value;
    }

    public static function form(Schema $schema): Schema
    {
        return AppointmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppointmentsTable::configure($table);
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
            'index' => ListAppointments::route('/'),
            'create' => CreateAppointment::route('/create'),
            'view' => ViewAppointment::route('/{record}'),
            // 'edit' => EditAppointment::route('/{record}/edit'),
            'prescription-create' => CreateAppointmentsPrescription::route('/{record}/prescription-create'),
            'prescription-edit' => EditAppointmentsPrescription::route('/prescription/{record}/edit'),
            'prescription-medicine-show' => ViewAppointmentsPrescription::route('/prescription-medicine-show/{record}'),
        ];
    }
}
