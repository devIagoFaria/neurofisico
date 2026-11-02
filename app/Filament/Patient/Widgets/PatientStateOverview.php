<?php

namespace App\Filament\Patient\Widgets;

use App\Filament\Doctor\Resources\LiveConsultations\LiveConsultationsResource;
use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use App\Models\LiveConsultation;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PatientStateOverview extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';
    protected function getStats(): array
    {
        $patientId = auth()->user()->patient->id;
        $todayDate = Carbon::now()->format('Y-m-d');

        $todayAppointmentCount = Appointment::where('patient_id', $patientId)->whereDate('date', $todayDate)->whereNotIn('status', [Appointment::CANCELLED])->count();
        $upcomingAppointmentCount = Appointment::where('patient_id', $patientId)->whereDate('date', '>', $todayDate)->whereNotIn('status', [Appointment::CANCELLED])->count();

        $todayCompleted = Appointment::wherePatientId($patientId)
            ->where('date', '=', $todayDate)
            ->whereStatus(Appointment::CHECK_OUT)
            ->count();
        $pastCompletedAppointmentCount = Appointment::wherePatientId($patientId)
            ->where('date', '<', $todayDate)
            ->count();
        $completedAppointmentCount = $pastCompletedAppointmentCount + $todayCompleted;

        $totalLiveConsultationsCount = LiveConsultation::where('patient_id', $patientId)->where('status', '!=', LiveConsultation::STATUS_CANCELLED)->count();

        return [
            Stat::make('Today Appointments', $todayAppointmentCount)
                ->label(__('messages.admin_dashboard.today_appointments'))
                ->url(AppointmentResource::getUrl())
                ->extraAttributes(['class' => 'today-appointments']),
            Stat::make('Next Appointments', $upcomingAppointmentCount)
                ->label(__('messages.patient_dashboard.upcoming_appointments'))
                ->url(AppointmentResource::getUrl())
                ->extraAttributes(['class' => 'upcoming-appointments']),
            Stat::make('Complete Appointments', $completedAppointmentCount)
                ->label(__('messages.patient_dashboard.completed_appointments'))
                ->url(AppointmentResource::getUrl())
                ->extraAttributes(['class' => 'total-appointments']),
            // Stat::make('Total Live Consultations', $totalLiveConsultationsCount)
            //     ->label('Total Live Consultations')
            //     ->url(LiveConsultationsResource::getUrl())
            //     ->extraAttributes(['class' => 'total-live-consultations']),


        ];
    }
}
