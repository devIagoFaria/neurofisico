<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Livewire\AppointmentPrescriptionRelationTable;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Notification as ModelsNotification;
use App\Models\Patient;
use App\Models\Transaction;
use App\Models\UserGoogleAppointment;
use App\Repositories\GoogleCalendarRepository;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ViewAppointment extends ViewRecord
{
    protected static string $resource = AppointmentResource::class;

    public $status;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->status = $this->record->status;

        $this->authorizeAccess();

        if (! $this->hasInfolist()) {
            $this->fillForm();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }

    public function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make(__('messages.common.overview'))->schema([
                            TextEntry::make('appointment_unique_id')
                                ->label(__('messages.appointment.appointment_unique_id').':')
                                ->default(__('messages.common.n/a'))
                                ->badge()
                                ->color('warning'),
                            TextEntry::make('from_time')
                                ->label(__('messages.appointment.appointment_at').':')
                                ->formatStateUsing(function ($record) {
                                    return new HtmlString(
                                        '<div class="">
                                            <div class="">
                                                '.Carbon::parse($record->date)->format('d M Y').' '
                                            .Carbon::parse($record->from_time)->format('g:i A').' - '.
                                            Carbon::parse($record->to_time)->format('g:i A').'
                                            </div>
                                        </div>'
                                    );
                                })
                                ->badge()
                                ->color('primary'),
                            Select::make('status')
                                ->label(__('messages.appointment.status').':')
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

                                    $currentStatus = $record->status;

                                    return match ($value) {

                                        // BOOKED → always disabled
                                        Appointment::BOOKED => true,

                                        // CHECK IN → disabled if CANCELLED or CHECK OUT
                                        Appointment::CHECK_IN => in_array($currentStatus, [
                                            Appointment::CANCELLED,
                                            Appointment::CHECK_OUT,
                                        ]),

                                        // CHECK OUT → disabled if CANCELLED or BOOKED
                                        Appointment::CHECK_OUT => in_array($currentStatus, [
                                            Appointment::CANCELLED,
                                            Appointment::BOOKED,
                                        ]),

                                        // CANCELLED → disabled if CHECK IN or CHECK OUT
                                        Appointment::CANCELLED => in_array($currentStatus, [
                                            Appointment::CHECK_IN,
                                            Appointment::CHECK_OUT,
                                        ]),

                                        default => false,
                                    };
                                })
                                ->live()
                                ->afterStateUpdated(function ($state, $record) {
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

                                    $appointment = Appointment::find($record->id);

                                    if (! $appointment || $appointment->status == $state) {
                                        return;
                                    }

                                    $appointment->update([
                                        'status' => $state,
                                    ]);

                                    $this->status = $state;

                                    $fullTime = $appointment->from_time.''.$appointment->from_time_type.' - '.$appointment->to_time.''.$appointment->to_time_type.' '.' '.Carbon::parse($appointment->date)->format('jS M, Y');

                                    $patient = Patient::whereId($appointment->patient_id)->with('user')->first();
                                    $doctor = Doctor::whereId($appointment->doctor_id)->with('user')->first();
                                    if ($this->status == Appointment::CHECK_OUT) {
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
                                    } elseif ($this->status == Appointment::CANCELLED) {
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
                                            'title' => $patient->user->full_name.'\'s appointment cancelled by'.getLogInUser()->full_name.' at '.$fullTime,
                                            'type' => ModelsNotification::CANCELED,
                                            'user_id' => $doctor->user_id,
                                        ]);
                                    }

                                    Notification::make()
                                        ->title(__('messages.flash.status_update'))
                                        ->success()
                                        ->send();
                                })
                                ->extraAttributes(['class' => 'no-cancel', 'style' => 'max-width: 190px'])
                                ->hidden(fn () => isRole('patient')),
                            TextEntry::make('status_text')
                                ->label(__('messages.appointment.status').':')
                                ->state(fn (Appointment $record) => $record->status)
                                ->badge()
                                ->formatStateUsing(function ($state) {
                                    return match ($state) {
                                        Appointment::BOOKED => __('messages.appointment.booked'),
                                        Appointment::CHECK_IN => __('messages.appointment.check_in'),
                                        Appointment::CHECK_OUT => __('messages.appointment.check_out'),
                                        Appointment::CANCELLED => __('messages.common.cancelled'),
                                    };
                                })
                                ->color(fn ($state) => match ($state) {
                                    Appointment::BOOKED => 'primary',
                                    Appointment::CHECK_IN => 'success',
                                    Appointment::CHECK_OUT => 'warning',
                                    Appointment::CANCELLED => 'danger',
                                })
                                ->visible(fn () => isRole('patient')),
                            TextEntry::make('patient.user.full_name')
                                ->label(__('messages.appointment.patient').':')
                                ->formatStateUsing(function ($record) {
                                    $patientId = $record->patient->user->id;
                                    $patientName = $record->patient->user->full_name;
                                    $url = '';

                                    if (Auth::user()->hasRole('clinic_admin')) {
                                        $url = \App\Filament\Resources\Patients\PatientResource::getUrl('view', ['record' => $patientId]);
                                    }

                                    return $url ? "<a href='{$url}' class='text-primary-600 hover:no-underline'>{$patientName}</a>" : $patientName;
                                })
                                ->html()
                                ->hidden(fn () => isRole('patient')),
                            TextEntry::make('doctor.user.full_name')
                                ->label(__('messages.doctor.doctor').':')
                                ->formatStateUsing(function ($record) {
                                    $doctorId = $record->doctor->user->id;
                                    $doctorName = $record->doctor->user->full_name;
                                    $url = '';
                                    if (Auth::user()->hasRole('clinic_admin')) {
                                        $url = \App\Filament\Resources\Doctors\DoctorResource::getUrl('view', ['record' => $doctorId]);
                                    }

                                    return $url ? "<a href='{$url}' class='text-primary-600 hover:no-underline'>{$doctorName}</a>" : $doctorName;
                                })
                                ->html(),
                            TextEntry::make('services.name')
                                ->label(__('messages.appointment.service').':'),
                            TextEntry::make('payable_amount')
                                ->label(__('messages.doctor_appointment.amount').':')
                                ->formatStateUsing(fn ($record) => getCurrencyIcon($record->currency_id).$record->payable_amount),
                            // ViewEntry::make('payment')
                            //     ->view('livewire.appointment-payment-status')
                            //     ->viewData(['appointment' => fn () => $this->getRecord(), 'label' => __('messages.appointment.payment').':'])
                            //     ->hidden(fn () => isRole('patient')),
                            // TextEntry::make('payment_text')
                            //     ->label(__('messages.appointment.payment').':')
                            //     ->state(fn ($record) => $record->payment_type)
                            //     ->formatStateUsing(fn ($state) => Appointment::PAYMENT_TYPE[$state])
                            //     ->color(fn ($state) => match ($state) {
                            //         Appointment::PAID => 'success',
                            //         Appointment::PENDING => 'danger',
                            //     })
                            //     ->badge()
                            //     ->visible(fn () => isRole('patient')),
                            TextEntry::make('payment_method')
                                ->label(__('messages.appointment.payment_method').':')
                                ->formatStateUsing(fn ($record) => Appointment::PAYMENT_METHOD[$record->payment_method])
                                ->visible(
                                    fn ($record) => ! (
                                        isRole('patient') &&
                                        $record->payment_type === Appointment::PENDING
                                    )
                                ),
                            TextEntry::make('created_at')
                                ->label(__('messages.web.created_at').':')
                                ->since(),
                        ])->columns(2),
                        // Tab::make(__('messages.prescription.prescriptions'))->schema([
                        //     Livewire::make(AppointmentPrescriptionRelationTable::class),
                        // ]),
                    ])->columnSpanFull(),
            ]);
    }

    public function paymentStatusUpdate($id)
    {
        $this->mountAction('selectPaymentMethod');
    }

    protected function getActions(): array
    {
        return [
            $this->selectPaymentMethodAction(),
        ];
    }

    public function selectPaymentMethodAction(): Action
    {
        return Action::make('selectPaymentMethod')
            ->label(__('messages.appointment.Select_payment_method'))
            ->modal()
            ->modalWidth('md')
            ->form([
                Select::make('payment_method')
                    ->label(__('messages.appointment.payment_method'))
                    ->options(function () {
                        $methods = [];
                        if (getSettingValue('manual_payment_enabled')) {
                            $methods[Appointment::MANUALLY] = 'Manually';
                        }
                        if (getSettingValue('stripe_enable')) {
                            $methods[Appointment::STRIPE] = 'Stripe';
                        }
                        if (getSettingValue('paystack_enable')) {
                            $methods[Appointment::PAYSTACK] = 'Paystack';
                        }
                        if (getSettingValue('paypal_enable')) {
                            $methods[Appointment::PAYPAL] = 'Paypal';
                        }
                        if (getSettingValue('razorpay_enable')) {
                            $methods[Appointment::RAZORPAY] = 'Razorpay';
                        }
                        if (getSettingValue('authorize_enable')) {
                            $methods[Appointment::AUTHORIZE] = 'Authorize';
                        }
                        return $methods;
                    })
                    ->required()
                    ->native(false)
                    ->columnSpanFull(),
            ])
            ->action(function (array $data) {
                $appointment = $this->getRecord();
                $loginUser = getLogInUser();

                if ($loginUser->hasRole('doctor')) {
                    $doctorExists = Appointment::whereId($appointment->id)
                        ->whereDoctorId($loginUser->doctor->id)
                        ->exists();

                    if (! $doctorExists) {
                        Notification::make()
                            ->title(__('messages.common.not_allow__assess_record'))
                            ->danger()
                            ->send();

                        return;
                    }
                }

                $appointment->load('patient');

                $appointment->update([
                    'payment_type' => Appointment::PAID,
                    'payment_method' => $data['payment_method'],
                ]);

                $transaction = Transaction::whereAppointmentId($appointment->appointment_unique_id)->first();

                if (! $transaction) {
                    Transaction::create([
                        'user_id' => $appointment->patient->user_id,
                        'transaction_id' => Str::random(10),
                        'appointment_id' => $appointment->appointment_unique_id,
                        'amount' => $appointment->payable_amount,
                        'type' => Appointment::MANUALLY,
                        'status' => Transaction::SUCCESS,
                        'accepted_by' => $loginUser->id,
                    ]);
                } else {
                    $transaction->update([
                        'status' => Transaction::SUCCESS,
                        'accepted_by' => $loginUser->id,
                    ]);
                }

                $appointmentNotification = Transaction::with('acceptedPaymentUser')->whereAppointmentId($appointment['appointment_unique_id'])->first();

                $fullTime = $appointment->from_time.''.$appointment->from_time_type.' - '.$appointment->to_time.''.$appointment->to_time_type.' '.' '.Carbon::parse($appointment->date)->format('jS M, Y');
                $patient = Patient::whereId($appointment->patient_id)->with('user')->first();
                ModelsNotification::create([
                    'title' => $appointmentNotification->acceptedPaymentUser->full_name.' changed the payment status '.Appointment::PAYMENT_TYPE[Appointment::PENDING].' to '.Appointment::PAYMENT_TYPE[$appointment->payment_type].' for appointment '.$fullTime,
                    'type' => ModelsNotification::PAYMENT_DONE,
                    'user_id' => $patient->user_id,
                ]);

                Notification::make()
                    ->title(__('messages.flash.payment_status_updated'))
                    ->success()
                    ->send();
            });
    }
}
