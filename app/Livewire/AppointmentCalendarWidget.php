<?php

namespace App\Livewire;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Notification as ModelsNotification;
use App\Models\Patient;
use App\Models\UserGoogleAppointment;
use App\Repositories\GoogleCalendarRepository;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class AppointmentCalendarWidget extends FullCalendarWidget
{
    protected function headerActions(): array
    {
        return [
            Action::make('back')
                ->label(__('messages.common.back'))
                ->outlined()
                ->url(AppointmentResource::getUrl('index')),
        ];
    }

    public Model|string|null $model = Appointment::class;

    public function fetchEvents(array $fetchInfo): array
    {
        $startDate = Carbon::parse($fetchInfo['start'])->toDateString();
        $endDate = Carbon::parse($fetchInfo['end'])->toDateString();

        // Get the logged-in user's ID and role
        $authUser = Auth::user();
        $doctorId = null;
        $patientId = null;

        if ($authUser && in_array('doctor', $authUser->roles->pluck('name')->toArray())) {
            $doctor = Doctor::where('user_id', $authUser->id)->first();
            if ($doctor) {
                $doctorId = $doctor->id;
            }
        } elseif ($authUser && in_array('patient', $authUser->roles->pluck('name')->toArray())) {
            $patient = Patient::where('user_id', $authUser->id)->first();
            if ($patient) {
                $patientId = $patient->id;
            }
        }

        $query = Appointment::query()
            ->with(['patient.patientUser', 'doctor.user']);

        // Filter by doctor if we have a doctor ID
        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        // Filter by patient if we have a patient ID
        if ($patientId) {
            $query->where('patient_id', $patientId);
        }

        return $query->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->map(function (Appointment $appointment) {
                return [
                    'id' => $appointment->id,
                    'title' => $appointment->patient->patientUser->full_name ?? 'Appointment',
                    'start' => Carbon::createFromFormat(
                        'Y-m-d g:i A',
                        $appointment->date.' '.$appointment->from_time.' '.$appointment->from_time_type
                    )->toIso8601String(),
                    'end' => Carbon::createFromFormat(
                        'Y-m-d g:i A',
                        $appointment->date.' '.$appointment->to_time.' '.$appointment->to_time_type
                    )->toIso8601String(),
                ];
            })
            ->toArray();
    }

    protected function viewAction(): Action
    {
        return ViewAction::make()
            ->modalWidth('md')
            ->infolist($this->infolist())
            ->record(
                fn (array $arguments) => Appointment::find($arguments['event']['id'])
            )
            ->modalFooterActions([])
            ->modalHeading(__('messages.appointment.appointment_details'));
    }

    public function infolist(): array
    {
        return [
            Group::make([
                Grid::make(12)->schema([
                    Group::make([
                        TextEntry::make('doctor.user.full_name')
                            ->hiddenLabel()
                            ->state(fn ($record) => __('messages.doctor.doctor').': '.$record->doctor->user->full_name)
                            ->weight('bold')
                            ->size('md')
                            ->hidden(fn () => isRole('doctor')),

                        TextEntry::make('patient.user.full_name')
                            ->hiddenLabel()
                            ->state(fn ($record) => __('messages.appointment.patient').': '.$record->patient->user->full_name)
                            ->color('gray')
                            ->weight(fn () => in_array('doctor', Auth::user()->roles->pluck('name')->toArray()) ? 'bold' : 'normal')
                            ->size(fn () => in_array('doctor', Auth::user()->roles->pluck('name')->toArray()) ? 'md' : 'sm')
                            ->hidden(fn () => isRole('patient')),
                    ])->columnSpan(11),
                ]),
            ]),

            Grid::make(2)->schema([
                TextEntry::make('from_time')
                    ->label(__('messages.appointment.starts').':')
                    ->inlineLabel()
                    ->icon('heroicon-m-clock')
                    ->iconColor('success')
                    ->formatStateUsing(
                        fn ($record) => Carbon::parse($record->from_time)
                            ->format('d M, Y - g:i A')
                    )
                    ->columnSpan(12),

                TextEntry::make('to_time')
                    ->label(__('messages.appointment.ends').':')
                    ->inlineLabel()
                    ->icon('heroicon-m-clock')
                    ->iconColor('danger')
                    ->state(
                        fn ($record) => Carbon::parse($record->to_time)
                            ->addMinutes($record->duration ?? 30)
                            ->format('d M, Y - g:i A')
                    )
                    ->columnSpan(12),
            ]),

            Grid::make(2)->schema([
                TextEntry::make('appointment_unique_id')
                    ->label(__('messages.appointment.appointment_unique_id').':')
                    ->inlineLabel()
                    ->weight('bold')
                    ->columnSpan(12),

                TextEntry::make('services.name')
                    ->label(__('messages.appointment.service').':')
                    ->default(__('messages.common.n/a'))
                    ->inlineLabel()
                    ->weight('bold')
                    ->columnSpan(12),

                TextEntry::make('payable_amount')
                    ->label(__('messages.appointment.payable_amount').':')
                    ->inlineLabel()
                    ->formatStateUsing(fn ($record) => getCurrencyIcon($record->currency_id).number_format($record->payable_amount, 2))
                    ->weight('bold')
                    ->columnSpan(12),
            ]),

            Grid::make(1)->schema([
                ViewEntry::make('status')
                    ->view('livewire.appointment-status-dropdown', ['label' => __('messages.appointment.status').':']),
            ]),
        ];
    }

    public function changeStatus($appointmentId, $status)
    {
        $appointment = Appointment::find($appointmentId);

        if (! $appointment) {
            return Notification::make()
                ->danger()
                ->title(__('messages.flash.something_went_wrong'))
                ->send();
        }

        if (getLogInUser()->hasRole('doctor')) {
            $doctor = Appointment::whereId($appointment->id)->whereDoctorId(getLogInUser()->doctor->id);
            if (! $doctor->exists()) {
                return Notification::make()
                    ->danger()
                    ->title(__('messages.common.not_allow__assess_record'))
                    ->send();
            }
        }

        if ($appointment->status == $status) {
            return;
        }

        $appointment->update([
            'status' => $status,
        ]);

        $fullTime = $appointment->from_time.''.$appointment->from_time_type.' - '.$appointment->to_time.''.$appointment->to_time_type.' '.' '.Carbon::parse($appointment->date)->format('jS M, Y');

        $patient = Patient::whereId($appointment->patient_id)->with('user')->first();
        $doctor = Doctor::whereId($appointment->doctor_id)->with('user')->first();
        if ($status == Appointment::CHECK_OUT) {
            ModelsNotification::create([
                'title' => ModelsNotification::APPOINTMENT_CHECKOUT_PATIENT_MSG.' '.getLogInUser()->full_name,
                'type' => ModelsNotification::CHECKOUT,
                'user_id' => $patient->user_id,
            ]);
            ModelsNotification::create([
                'title' => $patient->user->full_name.'\'s appointment check out by '.getLogInUser()->full_name.' at '.$fullTime,
                'type' => ModelsNotification::CHECKOUT,
                'user_id' => $doctor->user_id,
            ]);
        } elseif ($status == Appointment::CANCELLED) {
            $events = UserGoogleAppointment::with(['user'])->where('appointment_id', $appointment->id)->get();

            /** @var GoogleCalendarRepository $repo */
            $repo = App::make(GoogleCalendarRepository::class);

            $repo->destroy($events);

            ModelsNotification::create([
                'title' => ModelsNotification::APPOINTMENT_CANCEL_PATIENT_MSG.' '.getLogInUser()->full_name,
                'type' => ModelsNotification::CANCELED,
                'user_id' => $patient->user_id,
            ]);
            ModelsNotification::create([
                'title' => $patient->user->full_name.'\'s appointment cancelled by '.getLogInUser()->full_name.' at '.$fullTime,
                'type' => ModelsNotification::CANCELED,
                'user_id' => $doctor->user_id,
            ]);
        }

        Notification::make()
            ->title(__('messages.flash.status_update'))
            ->success()
            ->send();

        $this->js('window.location.reload()');
    }
}
