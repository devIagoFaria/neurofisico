<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Resources\Doctors\DoctorResource;
use App\Filament\Resources\Patients\PatientResource;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StateOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int | array | null
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 2,
            'lg' => 4,
            'xl' => 4,
        ];
    }

    protected function getStats(): array
    {
        $todayDate = Carbon::now()->format('Y-m-d');
        $upcomingAppointmentCount = Appointment::where('date', '>', $todayDate)->count();
        $totalAppointmentCount = Appointment::count();
        $totalDoctorCount = User::toBase()->whereType(User::DOCTOR)->where('status', User::ACTIVE)->where('email_verified_at', '!=', null)->count();
        $totalPatientCount = User::toBase()->whereType(User::PATIENT)->where('status', User::ACTIVE)->where('email_verified_at', '!=', null)->count();

        return [
            Stat::make('Upcoming Appointments', $upcomingAppointmentCount)
                ->label(__('messages.patient_dashboard.upcoming_appointments'))
                ->url(AppointmentResource::getUrl())
                ->extraAttributes(['class' => 'upcoming-appointments']),

            Stat::make('Total Appointments', $totalAppointmentCount)
                ->label(__('messages.doctor_dashboard.total_appointments'))
                ->url(AppointmentResource::getUrl())
                ->extraAttributes(['class' => 'total-appointments']),

            Stat::make('Total Active Doctors', $totalDoctorCount)
                ->label(__('messages.purchase_medicine.total') . ' ' . __('messages.common.active') . ' ' . __('messages.doctors'))
                ->url(DoctorResource::getUrl())
                ->extraAttributes(['class' => 'total-active-doctors']),  
                
            Stat::make('Total Patients', $totalPatientCount)
                ->label(__('messages.purchase_medicine.total') . ' ' . __('messages.common.active') . ' ' . __('messages.visit.patient'))
                ->url(PatientResource::getUrl())
                ->extraAttributes(['class' => 'total-patients']),
        ];
    }
}
