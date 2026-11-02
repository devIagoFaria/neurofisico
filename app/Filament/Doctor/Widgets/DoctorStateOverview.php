<?php

namespace App\Filament\Doctor\Widgets;

use App\Filament\Doctor\Resources\LiveConsultations\LiveConsultationsResource;
use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use App\Models\LiveConsultation;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DoctorStateOverview extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';
    protected function getStats(): array
    {
        $doctorId = auth()->user()->doctor->id;
        $todayDate = Carbon::now()->format('Y-m-d');

        $totalAppointmentCount = Appointment::where('doctor_id', $doctorId)->whereNotIn('status', [Appointment::CANCELLED])->count();
        $todayAppointmentCount = Appointment::where('doctor_id', $doctorId)->whereDate('date', $todayDate)->whereNotIn('status', [Appointment::CANCELLED])->count();
        $nextAppointmentCount = Appointment::where('doctor_id', $doctorId)->whereDate('date', '>', $todayDate)->whereNotIn('status', [Appointment::CANCELLED])->count();
        $totalLiveConsultationsCount = LiveConsultation::where('doctor_id', $doctorId)->where('status', '!=', LiveConsultation::STATUS_CANCELLED)->count();

        return [
            Stat::make('Total Appointments', $totalAppointmentCount)
                ->label(__('messages.doctor_dashboard.total_appointments'))
                ->url(AppointmentResource::getUrl())
                ->extraAttributes(['class' => 'total-appointments']),
            Stat::make('Today Appointments', $todayAppointmentCount)
                ->label(__('messages.admin_dashboard.today_appointments'))
                ->url(AppointmentResource::getUrl())
                ->extraAttributes(['class' => 'today-appointments']),
            Stat::make('Next Appointments', $nextAppointmentCount)
                ->label(__('messages.patient_dashboard.next_appointment'))
                ->url(AppointmentResource::getUrl())
                ->extraAttributes(['class' => 'upcoming-appointments']),
            // Stat::make('Total Live Consultations', $totalLiveConsultationsCount)
            //     ->label('Total Live Consultations')
            //     ->url(LiveConsultationsResource::getUrl())
            //     ->extraAttributes(['class' => 'total-live-consultations']),
        ];
    }
}
