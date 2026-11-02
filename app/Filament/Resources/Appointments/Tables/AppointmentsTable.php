<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Filament\Resources\Doctors\DoctorResource;
use App\Filament\Resources\Patients\PatientResource;
use App\Filament\Tables\Columns\AppointmentStatusColumn;
use App\Filament\Tables\Columns\CustomIdentityColumn;
use App\Http\Controllers\PaystackController;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Notification as ModelsNotification;
use App\Models\Patient;
use App\Models\Transaction;
use App\Models\UserGoogleAppointment;
use App\Repositories\GoogleCalendarRepository;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (isRole('doctor')) {
                    $doctorId = Doctor::where('user_id', Auth::id())->value('id');

                    $query->where('doctor_id', $doctorId);
                }

                if (isRole('patient')) {
                    $patientId = Patient::where('user_id', Auth::id())->value('id');

                    $query->where('patient_id', $patientId);
                }
            })
            ->defaultSort('id', 'desc')
            ->columns(
                [
                    CustomIdentityColumn::make('doctor.user.first_name')
                        ->avatar(fn ($record) => $record->doctor->user->profile_image)
                        ->html()
                        ->primary(
                            fn ($record) => $record->doctor->user->full_name.
                                (
                                    isRole('patient')
                                    ? ''
                                    : ' '.rating($record->doctor?->reviews?->avg('rating') ?? 0)
                                )
                        )
                        ->primaryUrl(
                            fn ($record) => isRole('patient') ? null : DoctorResource::getUrl('view', ['record' => $record->doctor->user_id])
                        )
                        ->label(__('messages.doctor.doctor'))
                        ->color('#2b7fff')
                        ->toggleable(false)
                        ->secondary('doctor.user.email')
                        ->sortable(['first_name'])
                        ->searchable(['first_name', 'last_name', 'email'])
                        ->size('md')
                        ->hidden(fn ($record) => isRole('doctor')),
                    CustomIdentityColumn::make('patient.user.first_name')
                        ->avatar(fn ($record) => $record->patient->user->profile_image)
                        ->html()
                        ->primary(
                            fn ($record) => $record->patient->user->full_name.
                                (
                                    isRole('doctor')
                                    ? ''
                                    : ' '.rating($record->patient?->reviews?->avg('rating') ?? 0)
                                )
                        )
                        ->primaryUrl(fn ($record) => PatientResource::getUrl(
                            'view',
                            [
                                'record' => Auth::user()->hasRole('doctor')
                                    ? $record->patient->user->id
                                    : $record->patient->user->id,
                            ]
                        ))
                        ->label(__('messages.appointment.patient'))
                        ->color('#2b7fff')
                        ->toggleable(false)
                        ->secondary('patient.user.email')
                        ->sortable(['first_name'])
                        ->searchable(['first_name', 'last_name', 'email'])
                        ->size('md')
                        ->hidden(fn ($record) => isRole('patient')),
                    TextColumn::make('date')
                        ->label(__('messages.appointment.appointment_date'))
                        ->sortable()
                        ->formatStateUsing(function ($record) {
                            return new HtmlString(
                                '<div class="text-center">'
                                    .Carbon::parse($record->from_time)->format('H:i')
                                    .' - '
                                    .Carbon::parse($record->to_time)->format('H:i')
                                    .'<br>'
                                    .Carbon::parse($record->date)->format('d M Y')
                                    .'</div>'
                            );
                        })
                        ->html()
                        ->badge(),
                    // TextColumn::make('payable_amount')
                    //     ->label(__('messages.appointment.service_charge'))
                    //     ->sortable()
                    //     ->formatStateUsing(fn ($record) => getCurrencyFormat(getCurrencyCode(), $record->payable_amount))
                    //     ->alignCenter()
                    //     ->hidden(fn ($record) => isRole('clinic_admin')),
                    // SelectColumn::make('payment_type')
                    //     ->label(__('messages.appointment.payment_status'))
                    //     ->options([
                    //         Appointment::PENDING => __('messages.transaction.pending'),
                    //         Appointment::PAID => __('messages.transaction.paid'),
                    //     ])
                    //     ->disableOptionWhen(function ($value, $record) {

                    //         if (! $record instanceof Appointment) {
                    //             return false;
                    //         }

                    //         // Never disable currently selected value
                    //         if ((int) $value === (int) $record->payment_type) {
                    //             return false;
                    //         }

                    //         return match ($record->payment_type) {

                    //             Appointment::PENDING => false,

                    //             Appointment::PAID => true,

                    //             default => false,
                    //         };
                    //     })
                    //     ->selectablePlaceholder(false)
                    //     ->native(false)
                    //     ->updateStateUsing(function ($state, Appointment $record) {
                    //         if ((int) $record->payment_type === (int) $state) {
                    //             return $state;
                    //         }

                    //         if (getLogInUser()->hasRole('doctor')) {
                    //             $doctor = Appointment::whereId($record->id)->whereDoctorId(getLogInUser()->doctor->id);
                    //             if (! $doctor->exists()) {
                    //                 return Notification::make()
                    //                     ->danger()
                    //                     ->title(__('messages.common.not_allow__assess_record'))
                    //                     ->send();
                    //             }
                    //         }

                    //         $appointment = Appointment::with('patient')->findOrFail($record->id);
                    //         $transactionExist = Transaction::whereAppointmentId($appointment['appointment_unique_id'])->first();

                    //         $record->update([
                    //             'payment_type' => $state,
                    //             'payment_method' => Appointment::MANUALLY,
                    //         ]);

                    //         if (empty($transactionExist)) {
                    //             $transaction = [
                    //                 'user_id' => $appointment->patient->user_id,
                    //                 'transaction_id' => Str::random(10),
                    //                 'appointment_id' => $appointment->appointment_unique_id,
                    //                 'amount' => $appointment->payable_amount,
                    //                 'type' => Appointment::MANUALLY,
                    //                 'status' => Transaction::SUCCESS,
                    //                 'accepted_by' => Auth::user()->id,
                    //             ];

                    //             Transaction::create($transaction);
                    //         } else {
                    //             $transactionExist->update([
                    //                 'status' => Transaction::SUCCESS,
                    //                 'accepted_by' => Auth::user()->id,
                    //             ]);
                    //         }

                    //         $appointmentNotification = Transaction::with('acceptedPaymentUser')->whereAppointmentId($appointment['appointment_unique_id'])->first();
                    //         $fullTime = $appointment->from_time.''.$appointment->from_time_type.' - '.$appointment->to_time.''.$appointment->to_time_type.' '.' '.Carbon::parse($appointment->date)->format('jS M, Y');
                    //         $patient = Patient::whereId($appointment->patient_id)->with('user')->first();
                    //         ModelsNotification::create([
                    //             'title' => $appointmentNotification->acceptedPaymentUser->full_name.' changed the payment status '.Appointment::PAYMENT_TYPE[Appointment::PENDING].' to '.Appointment::PAYMENT_TYPE[$appointment->payment_type].' for appointment '.$fullTime,
                    //             'type' => ModelsNotification::PAYMENT_DONE,
                    //             'user_id' => $patient->user_id,
                    //         ]);

                    //         Notification::make()
                    //             ->title(__('messages.flash.status_update'))
                    //             ->success()
                    //             ->send();

                    //         return $state;
                    //     })
                    //     ->extraAttributes([
                    //         'style' => 'max-width:190px',
                    //     ])
                    //     ->visible(fn () => isRole('doctor')),

                    TextColumn::make('payment_type_text')
                        ->label(__('messages.appointment.payment_status'))
                        ->state(fn ($record) => $record->payment_type)
                        ->extraAttributes([
                            'class' => 'fi-badge px-2 py-1 text-black text-xs font-medium rounded-full max-w-[100px] text-center',
                        ])
                        ->formatStateUsing(fn ($state) => match ($state) {
                            Appointment::PENDING => __('messages.transaction.pending'),
                            Appointment::PAID => __('messages.transaction.paid'),
                        })
                        ->color(fn ($state) => match ($state) {
                            Appointment::PENDING => 'danger',
                            Appointment::PAID => 'success',
                        })
                        ->action(
                            Action::make('pay_now')
                                ->disabled(fn ($record) => $record->payment_type !== Appointment::PENDING)
                                ->modal()
                                ->modalHeading(__('messages.appointment.Select_payment_method'))
                                ->modalSubmitActionLabel(__('messages.common.save'))
                                ->modalCancelActionLabel(__('messages.common.cancel'))
                                ->modalWidth('md')
                                ->form([
                                    Select::make('payment_gateway')
                                        ->hiddenLabel()
                                        ->options(getAllPaymentStatus())
                                        // ->options(getPaymentGateway())
                                        ->required()
                                        ->native(false)
                                        ->live(),
                                ])
                                ->action(function ($record, array $data, $action) {

                                    $paymentGatewayType = $data['payment_gateway'];

                                    // Stripe
                                    if ($paymentGatewayType === Appointment::STRIPE) {
                                        try {
                                            // Validate required data
                                            if (empty($record->payable_amount) || $record->payable_amount <= 0) {
                                                Notification::make()
                                                    ->title(__('Invalid appointment amount'))
                                                    ->danger()
                                                    ->send();

                                                return;
                                            }

                                            // Set Stripe API key
                                            Stripe::setApiKey(getSettingValue('stripe_secret') ?? config('services.stripe.secret_key'));

                                            // Prepare line items
                                            $currency = strtolower(getCurrencyCode());
                                            $amount = in_array(strtoupper($currency), zeroDecimalCurrencies())
                                                ? (int) $record->payable_amount
                                                : (int) ($record->payable_amount * 100);

                                            $session = StripeSession::create([
                                                'mode' => 'payment',
                                                'payment_method_types' => ['card'],
                                                'line_items' => [
                                                    [
                                                        'price_data' => [
                                                            'currency' => $currency,
                                                            'unit_amount' => $amount,
                                                            'product_data' => [
                                                                'name' => 'Appointment #'.$record->id,
                                                                'description' => 'Medical appointment payment',
                                                                'metadata' => [
                                                                    'appointment_id' => $record->id,
                                                                    'doctor_id' => $record->doctor_id,
                                                                    'patient_id' => $record->patient_id,
                                                                ],
                                                            ],
                                                        ],
                                                        'quantity' => 1,
                                                    ],
                                                ],
                                                'metadata' => [
                                                    'appointment_id' => $record->id,
                                                    'patient_id' => $record->patient_id,
                                                    'doctor_id' => $record->doctor_id,
                                                ],
                                                'customer_email' => $record->patient->user->email ?? null,
                                                'success_url' => route('stripe.success').'?session_id={CHECKOUT_SESSION_ID}',
                                                'cancel_url' => route('stripe.cancel'),
                                                'expires_at' => time() + (30 * 60),
                                            ]);

                                            $record->update([
                                                'payment_method' => Appointment::STRIPE,
                                                'transaction_id' => $session->id,
                                            ]);
                                            session(['user_id' => getLogInUserId()]);
                                            return redirect()->away($session->url);
                                        } catch (\Exception $e) {
                                            Log::error('Stripe payment error: '.$e->getMessage(), [
                                                'appointment_id' => $record->id,
                                                'patient_id' => $record->patient_id,
                                                'amount' => $record->payable_amount,
                                            ]);
                                        }
                                    }

                                    // PayPal
                                    if ($paymentGatewayType === Appointment::PAYPAL) {
                                        return redirect()->route('paypal.init', [
                                            'appointmentId' => $record->id,
                                        ]);
                                    }

                                    // Razorpay
                                    if ($paymentGatewayType === Appointment::RAZORPAY) {
                                        return redirect()->route('razorpay.init', [
                                            'appointmentId' => $record->id,
                                        ]);
                                    }

                                    // Paystack
                                    if ($paymentGatewayType === Appointment::PAYSTACK) {
                                        if (! empty(getSettingValue('paystack_key')) && ! empty(getSettingValue('paystack_secret')) && ! empty(getSettingValue('paystack_enable'))) {
                                            if (paystackCurrencySupports() == false) {
                                                return Notification::make()
                                                    ->title(__('messages.payment_method.paystack').' '.__('messages.flash.currency_not_supported'))
                                                    ->danger()
                                                    ->send();

                                            }

                                            $paystackController = app(PaystackController::class);
                                            $result = $paystackController->redirectToGateway($record);

                                            if (session('session_url')) {
                                                return redirect()->away(session('session_url'));
                                            } else {
                                                return Notification::make()
                                                    ->title(__('messages.payment_method.paystack').' '.__('messages.flash.something_went_wrong'))
                                                    ->danger()
                                                    ->send();
                                            }
                                        } else {
                                            return Notification::make()
                                                ->title(__('messages.payment_method.paystack').' '.__('messages.paypal.credentials_not_set'))
                                                ->danger()
                                                ->send();
                                        }
                                    }

                                    if ($paymentGatewayType === Appointment::AUTHORIZE) {
                                        return redirect()->route('authorize.init', [
                                            'appointmentId' => $record->id,
                                        ]);
                                    }
                                    // For other payment methods, update the record directly
                                    $record->update([
                                        'payment_method' => $paymentGatewayType,
                                        'payment_type' => Appointment::PAID,
                                    ]);

                                    Notification::make()
                                        ->title(__('messages.flash.payment_status_updated'))
                                        ->success()
                                        ->send();
                                })

                        )
                        ->visible(fn () => isRole('admin') || isRole('clinic_admin')),


                    // TextColumn::make('payment_status')
                    //     ->label(__('messages.appointment.payment'))
                    //     ->state(fn (Appointment $record) => $record->payment_type)
                    //     ->extraAttributes([
                    //         'class' => 'fi-badge px-2 py-1 text-black text-xs font-medium rounded-full max-w-[100px] text-center',
                    //     ])
                    //     ->formatStateUsing(fn ($state) => match ($state) {
                    //         Appointment::PENDING => __('messages.transaction.pending'),
                    //         Appointment::PAID => __('messages.transaction.paid'),
                    //         default => '-',
                    //     })
                    //     ->color(fn ($state) => match ($state) {
                    //         Appointment::PENDING => 'danger',
                    //         Appointment::PAID => 'success',
                    //         default => 'gray',
                    //     })
                    //     ->visible(fn () => isRole('patient')),
                    AppointmentStatusColumn::make('status')
                        ->label(__('messages.appointment.status'))
                        ->visible(fn ($record) => isRole('doctor') || isRole('clinic_admin') || isRole('admin')),

                    TextColumn::make('status_text')
                        ->label(__('messages.appointment.status'))
                        ->state(fn (Appointment $record) => $record->status)
                        ->extraAttributes([
                            'class' => 'fi-badge px-2 py-1 text-black text-xs font-medium rounded-full max-w-[100px] text-center',
                        ])
                        ->formatStateUsing(
                            fn ($state) => match ($state) {
                                Appointment::BOOKED => __('messages.appointment.booked'),
                                Appointment::CHECK_IN => __('messages.appointment.check_in'),
                                Appointment::CHECK_OUT => __('messages.appointment.check_out'),
                                Appointment::CANCELLED => __('messages.common.cancelled'),
                            }
                        )
                        ->color(fn ($state) => match ($state) {
                            Appointment::BOOKED => 'primary',
                            Appointment::CHECK_IN => 'success',
                            Appointment::CHECK_OUT => 'warning',
                            Appointment::CANCELLED => 'danger',
                        })
                        ->visible(fn () => isRole('patient')),
                ],
            )->recordActionsColumnLabel(__('messages.common.action'))
            ->filters([
                // SelectFilter::make('payment_type')
                //     ->label(__('messages.medicine_bills.payment_type'))
                //     ->native(false)
                //     ->options([
                //         Appointment::PENDING => __('messages.transaction.pending'),
                //         Appointment::PAID => __('messages.transaction.paid'),
                //     ]),
                SelectFilter::make('status')
                    ->label(__('messages.appointment.status'))
                    ->native(false)
                    ->default(__('messages.appointment.booked'))
                    ->options([
                        Appointment::ALL => __('messages.doctor_session.all'),
                        Appointment::BOOKED => __('messages.appointment.booked'),
                        Appointment::CHECK_IN => __('messages.appointment.check_in'),
                        Appointment::CHECK_OUT => __('messages.appointment.check_out'),
                        Appointment::CANCELLED => __('messages.appointment.cancelled'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value']) && $data['value'] !== null && $data['value'] !== '') {
                            if ((int) $data['value'] === Appointment::ALL) {
                                return;
                            }
                            $query->where('status', $data['value']);
                        }
                    }),
                // DateRangeFilter::make('date')
                //     ->label(__('messages.appointment.date'))
                //     ->placeholder(__('messages.common.pick_date_range'))
                //     ->startDate(Carbon::now()->startOfWeek()->subDay())
                //     ->endDate(Carbon::now()->endOfWeek()->subDay())
                //     ->query(function (Builder $query, ?Carbon $startDate, ?Carbon $endDate) {
                //         if ($startDate && $endDate) {
                //             $query->where('date', '>=', $startDate->format('Y-m-d'))
                //                   ->where('date', '<=', $endDate->format('Y-m-d'));
                //         }
                //     }),
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_appointment'))
            ->deferFilters(false)
            ->recordUrl(null)
            ->recordActions([
                // Action::make('download_pdf')
                //     ->label(__('messages.common.download'))
                //     ->icon(Phosphor::Download)
                //     ->iconButton()
                //     ->url(fn ($record) => route('admin.appointmentPdf', $record->id))
                //     ->openUrlInNewTab(),
                ViewAction::make()->iconButton(),
                DeleteAction::make()
                    ->iconButton()
                    ->successNotificationTitle(__('messages.flash.appointment_delete'))
                    ->hidden(fn ($record) => isRole('doctor')),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
