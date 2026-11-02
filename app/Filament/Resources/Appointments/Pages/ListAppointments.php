<?php

namespace App\Filament\Resources\Appointments\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Appointments\AppointmentResource;
use Illuminate\Support\Facades\Auth;

class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('googleCalendar')
                ->hiddenLabel()
                ->url(fn() => in_array('doctor', Auth::user()->roles->pluck('name')->toArray())
                    ? route('filament.doctor.pages.appointment-calendar')
                    : (in_array('patient', Auth::user()->roles->pluck('name')->toArray())
                        ? route('filament.patient.pages.appointment-calendar')
                        : route('filament.admin.pages.appointment-calendar')))
                ->icon('heroicon-s-calendar'),
            CreateAction::make(),
        ];
    }

    public function updateAppointmentStatus($recordId, $state)
    {
        $record = \App\Models\Appointment::find($recordId);

        if (! $record) {
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title(__('messages.flash.something_went_wrong'))
                ->send();
            return;
        }

        if (getLogInUser()->hasRole('doctor')) {
            $doctor = \App\Models\Appointment::whereId($record->id)->whereDoctorId(getLogInUser()->doctor->id);
            if (! $doctor->exists()) {
                \Filament\Notifications\Notification::make()
                    ->danger()
                    ->title(__('messages.common.not_allow__assess_record'))
                    ->send();
                return;
            }
        }

        $record->update([
            'status' => $state,
        ]);

        $fullTime = $record->from_time.''.$record->from_time_type.' - '.$record->to_time.''.$record->to_time_type.' '.' '.\Carbon\Carbon::parse($record->date)->format('jS M, Y');

        $patient = \App\Models\Patient::whereId($record->patient_id)->with('user')->first();
        $doctor = \App\Models\Doctor::whereId($record->doctor_id)->with('user')->first();

        if ($state == \App\Models\Appointment::CHECK_OUT) {
            \App\Models\Notification::create([
                'title' => \App\Models\Notification::APPOINTMENT_CHECKOUT_PATIENT_MSG.' '.getLogInUser()->full_name,
                'type' => \App\Models\Notification::CHECKOUT,
                'user_id' => $patient->user_id,
            ]);
            \App\Models\Notification::create([
                'title' => $patient->user->full_name.'\'s appointment check out by '.getLogInUser()->full_name.' at '.$fullTime,
                'type' => \App\Models\Notification::CHECKOUT,
                'user_id' => $doctor->user_id,
            ]);
        } elseif ($state == \App\Models\Appointment::CANCELLED) {
            $events = \App\Models\UserGoogleAppointment::with(['user'])->where('appointment_id', $record->id)->get();

            /** @var \App\Repositories\GoogleCalendarRepository $repo */
            $repo = \Illuminate\Support\Facades\App::make(\App\Repositories\GoogleCalendarRepository::class);

            $repo->destroy($events);

            \App\Models\Notification::create([
                'title' => \App\Models\Notification::APPOINTMENT_CANCEL_PATIENT_MSG.' '.getLogInUser()->full_name,
                'type' => \App\Models\Notification::CANCELED,
                'user_id' => $patient->user_id,
            ]);
            \App\Models\Notification::create([
                'title' => $patient->user->full_name.'\'s appointment cancelled by '.getLogInUser()->full_name.' at '.$fullTime,
                'type' => \App\Models\Notification::CANCELED,
                'user_id' => $doctor->user_id,
            ]);
        }

        \Filament\Notifications\Notification::make()
            ->title(__('messages.flash.status_update'))
            ->success()
            ->send();
    }
}
