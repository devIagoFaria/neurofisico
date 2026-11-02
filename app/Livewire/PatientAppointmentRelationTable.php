<?php

namespace App\Livewire;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Notification as ModelsNotification;
use App\Models\Patient;
use App\Models\User;
use App\Models\UserGoogleAppointment;
use App\Repositories\GoogleCalendarRepository;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\HtmlString;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class PatientAppointmentRelationTable extends TableWidget
{
    public $record;

    public $id;

    public $status;

    public function mount()
    {
        $this->id = Route::current()->parameter('record');
        $this->status = $this->id;
    }

    public function GetRecord()
    {
        $patient = Patient::where('user_id', $this->id)->firstOrFail();

        if (Auth::user()->hasRole('doctor')) {
            $doctor = Doctor::where('user_id', Auth::id())->firstOrFail();

            return Appointment::where('doctor_id', $doctor->id)
                ->where('patient_id', $patient->id);
        }

        return Appointment::where('patient_id', $patient->id);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->GetRecord())
            ->defaultSort('id', 'desc')
            ->heading('')
            ->searchable(false)
            ->paginated([5, 10])
            ->columns([
                SpatieMediaLibraryImageColumn::make('doctor.doctorUser.profile')
                    ->label(__('messages.doctor.doctor'))
                    ->circular()
                    ->defaultImageUrl(function ($record) {
                        if (! $record->doctor->user->hasMedia(User::PROFILE)) {
                            return getUserImageInitial($record->id, $record->doctor->user->full_name);
                        }
                    })
                    ->collection(User::PROFILE)
                    ->disk(config('app.media_disk'))
                    ->width(50)->imageHeight(50),
                TextColumn::make('doctor.doctorUser.full_name')
                    ->label('')
                    ->html()
                    ->color('primary')
                    ->weight(FontWeight::SemiBold)
                    ->description(fn ($record) => $record->doctor->doctorUser->email ?? __('messages.common.n/a'))
                    ->searchable(['first_name', 'last_name', 'email']),

                TextColumn::make('date')
                    ->label(__('messages.appointment.appointment_date'))
                    ->formatStateUsing(function ($record) {
                        return new HtmlString(
                            '<div class="text-center">'
                                .Carbon::parse($record->from_time)->format('g:i A')
                                .' - '
                                .Carbon::parse($record->to_time)->format('g:i A')
                                .'<br>'
                                .Carbon::parse($record->date)->format('d M Y')
                                .'</div>'
                        );
                    })
                    ->html()
                    ->badge(),
                SelectColumn::make('status')
                    ->label(__('messages.appointment.status'))
                    ->options([
                        Appointment::BOOKED => __('messages.appointment.booked'),
                        Appointment::CHECK_IN => __('messages.appointment.check_in'),
                        Appointment::CHECK_OUT => __('messages.appointment.check_out'),
                        Appointment::CANCELLED => __('messages.common.cancelled'),
                    ])
                    ->native(false)
                    ->disableOptionWhen(function ($value, $record) {

                        if (! $record) {
                            return false;
                        }

                        $status = $record->status;

                        return match ($value) {

                            Appointment::BOOKED => true,

                            Appointment::CHECK_IN => in_array($status, [
                                Appointment::CANCELLED,
                                Appointment::CHECK_OUT,
                            ]),

                            Appointment::CHECK_OUT => in_array($status, [
                                Appointment::CANCELLED,
                                Appointment::BOOKED,
                            ]),

                            Appointment::CANCELLED => in_array($status, [
                                Appointment::CHECK_IN,
                                Appointment::CHECK_OUT,
                            ]),

                            default => false,
                        };
                    })->beforeStateUpdated(function ($state, $record) {
                        if (! $record) {
                            return Notification::make()
                                ->danger()
                                ->title(__('messages.flash.something_went_wrong'))
                                ->send();
                        }

                        if (getLogInUser()->hasRole('doctor')) {
                            $doctor = Appointment::whereId($record->id)->whereDoctorId(getLogInUser()->doctor->id);
                            if (! $doctor->exists()) {
                                return Notification::make()
                                    ->danger()
                                    ->title(__('messages.common.not_allow__assess_record'))
                                    ->send();
                            }
                        }
                    })
                    ->afterStateUpdated(function ($state, $record) {
                        if (! $record) {
                            return Notification::make()
                                ->danger()
                                ->title(__('messages.flash.something_went_wrong'))
                                ->send();
                        }

                        $record->update([
                            'status' => $state,
                        ]);

                        $fullTime = $record->from_time.''.$record->from_time_type.' - '.$record->to_time.''.$record->to_time_type.' '.' '.Carbon::parse($record->date)->format('jS M, Y');

                        $patient = Patient::whereId($record->patient_id)->with('user')->first();
                        $doctor = Doctor::whereId($record->doctor_id)->with('user')->first();
                        if ($state == Appointment::CHECK_OUT) {
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
                        } elseif ($state == Appointment::CANCELLED) {
                            $events = UserGoogleAppointment::with(['user'])->where('appointment_id', $record->id)->get();

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
                    })
                    ->extraAttributes(['class' => 'no-cancel', 'style' => 'max-width: 190px']),

            ])
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->deferFilters(false)
            ->recordUrl(null)
            ->filters([
                SelectFilter::make('status')
                    ->label(__('messages.web.status'))
                    ->native(false)
                    ->options([
                        Appointment::ALL => __('messages.doctor_session.all'),
                        Appointment::BOOKED => __('messages.appointment.booked'),
                        Appointment::CHECK_IN => __('messages.appointment.check_in'),
                        Appointment::CHECK_OUT => __('messages.appointment.check_out'),
                        Appointment::CANCELLED => __('messages.appointment.cancelled'),
                    ])
                    ->default(__('messages.appointment.booked')),
                // DateRangeFilter::make('date')
                //     ->label(__('messages.appointment.date'))
                //     ->placeholder(__('messages.common.pick_date_range'))
                //     ->startDate(Carbon::now()->startOfWeek()->subDay())
                //     ->endDate(Carbon::now()->endOfWeek()->subDay()),
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->url(
                        fn ($record) => AppointmentResource::getUrl('view', ['record' => $record->id])
                    ),
                DeleteAction::make()
                    ->iconButton(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     //
                // ]),
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_appointment'));
    }

    public function changeStatus($appointmentId, $status)
    {
        $appointment = Appointment::find($appointmentId);

        if (! $appointment) {
            return;
        }

        $appointment->update([
            'status' => $status,
        ]);

        Notification::make()
            ->title(__('messages.flash.status_update'))
            ->success()
            ->send();
    }
}
