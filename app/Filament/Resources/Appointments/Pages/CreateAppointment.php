<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Events\CreateGoogleAppointment;
use App\Filament\Resources\Appointments\AppointmentResource;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\PaystackController;
use App\Mail\DoctorAppointmentBookMail;
use App\Mail\PatientAppointmentBookMail;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Setting;
use App\Utils\ResponseUtil;
use Carbon\Carbon;
use Exception;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected static bool $canCreateAnother = false;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl('index')),
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        $actualPaymentType = '';
        $input = $data;
        // try {
        DB::beginTransaction();
        $input['appointment_unique_id'] = strtoupper(Appointment::generateAppointmentUniqueId());
        $fromTime = explode(' ', $input['from_time']);
        $toTime = explode(' ', $input['to_time']);
        $input['from_time'] = $fromTime[0];
        $input['from_time_type'] = '';
        $input['to_time'] = $toTime[0];
        $input['to_time_type'] = '';
        $input['payment_type'] = Appointment::MANUALLY;
        $input['payment_method'] = Appointment::MANUALLY;

        $appointment = Appointment::create($input);
        $patient = Patient::whereId($input['patient_id'])->with('user')->first();
        $input['patient_name'] = $patient->user->full_name;
        $input['original_from_time'] = $fromTime[0];
        $input['original_to_time'] = $toTime[0];
        $service = Service::whereId($input['service_id'])->first();
        $input['service'] = $service->name;

        // if ($patient->user->email_notification) {
        //     Mail::to($patient->user->email)->send(new PatientAppointmentBookMail($input));
        // }

        $input['full_time'] = $input['original_from_time'].'-'.$input['original_to_time'].' '.Carbon::parse($input['date'])->format('jS M, Y');
        if (! getLogInUser()->hasRole('patient')) {
            $patientNotification = Notification::create([
                'title' => Notification::APPOINTMENT_CREATE_PATIENT_MSG.' '.$input['full_time'],
                'type' => Notification::BOOKED,
                'user_id' => $patient->user->id,
            ]);
        }

        $doctor = Doctor::whereId($input['doctor_id'])->with('user')->first();
        $input['doctor_name'] = $doctor->user->full_name;
        if ($doctor->user->email_notification) {
            Mail::to($doctor->user->email)->send(new DoctorAppointmentBookMail($input));
        }

        $doctorNotification = Notification::create([
            'title' => $patient->user->full_name.' '.Notification::APPOINTMENT_CREATE_DOCTOR_MSG.' '.$input['full_time'],
            'type' => Notification::BOOKED,
            'user_id' => $doctor->user->id,
        ]);

        DB::commit();

        try {
            CreateGoogleAppointment::dispatch(true, $appointment->id);
            CreateGoogleAppointment::dispatch(false, $appointment->id);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }

        $inputForGateway = $input;
        $inputForGateway['payment_type'] = $actualPaymentType;

        $this->paymentGateway($appointment, $inputForGateway);

        return $appointment;
        // } catch (Exception $e) {
        //     DB::rollBack();
        //     notify('Something went wrong', $e->getMessage(), 'danger');
        //     $this->halt();
        // }

        return $appointment;
    }

    private function paymentGateway($appointment, $input)
    {
        $paymentType = $input['payment_type'];
        $settings = Setting::pluck('value', 'key')->toArray();

        // Handle payment method integration exactly as in the cloneable project's AppointmentController@store method
        if ($paymentType == Appointment::STRIPE) {
            if (! empty($settings['stripe_enable']) && ! empty($settings['stripe_key']) && ! empty($settings['stripe_secret'])) {
                $this->createSession($appointment);
            } else {
                FilamentNotification::make()
                    ->title(__('messages.payment_method.stripe').' '.__('messages.paypal.credentials_not_set'))
                    ->danger()
                    ->send();

                $this->halt();
            }
        }

        if ($paymentType == Appointment::PAYSTACK) {
            if (! empty($settings['paystack_enable']) && ! empty($settings['paystack_key']) && ! empty($settings['paystack_secret'])) {
                if (paystackCurrencySupports() == false) {
                    FilamentNotification::make()
                        ->title(__('messages.payment_method.paystack').' '.__('messages.flash.currency_not_supported'))
                        ->danger()
                        ->send();
                    $this->halt();
                } else {
                    $paystackController = app(PaystackController::class);
                    $result = $paystackController->redirectToGateway($appointment);
                }

            } else {
                FilamentNotification::make()
                    ->title(__('messages.payment_method.paystack').' '.__('messages.paypal.credentials_not_set'))
                    ->danger()
                    ->send();
                $this->halt();
            }
        }

        if ($paymentType == Appointment::PAYPAL) {
            if (! empty($settings['paypal_enable']) && ! empty($settings['paypal_key']) && ! empty($settings['paypal_secret'])) {

                if (paypalCurrencySupports() === false) {
                    FilamentNotification::make()
                        ->title(__('messages.flash.currency_not_supported'))
                        ->danger()
                        ->send();
                    $this->halt();
                } else {
                    $paypalController = app(PaypalController::class);
                    $result = $paypalController->createSession($appointment);
                }

            } else {
                FilamentNotification::make()
                    ->title(__('messages.payment_method.paypal').' '.__('messages.paypal.credentials_not_set'))
                    ->danger()
                    ->send();
                $this->halt();
            }
        }

        if ($paymentType == Appointment::RAZORPAY) {
            if (! empty($settings['razorpay_enable']) && ! empty($settings['razorpay_key']) && ! empty($settings['razorpay_secret'])) {
                if (razorpayCurrencySupports() == false) {
                    FilamentNotification::make()
                        ->title(__('messages.payment_method.razorpay').' '.__('messages.flash.currency_not_supported'))
                        ->danger()
                        ->send();
                    $this->halt();
                }
                session([
                    'session_url' => route('razorpay.init', [
                        'appointmentId' => $appointment->id,
                    ]),
                ]);
            } else {
                FilamentNotification::make()
                    ->title(__('messages.payment_method.razorpay').' '.__('messages.paypal.credentials_not_set'))
                    ->danger()
                    ->send();
                $this->halt();
            }
        }

        if ($paymentType == Appointment::AUTHORIZE) {
            if (! empty($settings['authorize_enable']) && ! empty($settings['authorize_login_id']) && ! empty($settings['authorize_transaction_key'])) {
                $currencySupport = authorizedCurrencySupports();
                if ($currencySupport === false) {

                    FilamentNotification::make()
                        ->title(__('messages.payment_method.authorize_net').' '.__('messages.flash.currency_not_supported'))
                        ->danger()
                        ->send();
                    $this->halt();
                } else {
                    session(['session_url' => route('authorize.init', ['appointmentId' => $appointment->id])]);
                }
            } else {
                FilamentNotification::make()
                    ->title(__('messages.payment_method.authorize').' '.__('messages.paypal.credentials_not_set'))
                    ->danger()
                    ->send();
                $this->halt();
            }
        }

        // This PAYTM integration is currently not implemented in the cloneable project, but you can add it similarly to the other payment methods when needed
        // if ($paymentType == Appointment::PAYTM) {
        //     if (env('PAYTM_MERCHANT_ID') && env('PAYTM_MERCHANT_KEY')) {
        //         // Paytm payment processing would go here
        //         // In the cloneable project, this returns a JSON response
        //     } else {
        //         // Handle missing credentials error
        //     }
        // }

        // For manual payments, no special processing is needed
        // The appointment is created and payment status is handled separately
    }

    private function sendResponse($result, $message): JsonResponse
    {
        return response()->json(ResponseUtil::makeResponse($message, $result));
    }

    public function createSession($input)
    {
        $appointmentId = $input['appointment_unique_id'];
        $patientEmail = Patient::with('user')->whereId($input['patient_id'])->first();
        $doctorName = Doctor::with('user')->whereId($input['doctor_id'])->first();
        setStripeApiKey();

        $successUrl = '/medical-payment-success';
        $cancelUrl = '/medical-payment-failed';

        $session = Session::create([
            'payment_method_types' => ['card'],
            'customer_email' => $patientEmail->user->email,

            'line_items' => [
                [
                    'price_data' => [
                        'currency' => getCurrencyCode(),
                        'unit_amount' => in_array(getCurrencyCode(), zeroDecimalCurrencies())
                            ? $input['payable_amount']
                            : $input['payable_amount'] * 100,

                        'product_data' => [
                            'name' => 'Payment for appointment booking',
                            'description' => 'Payment for booking appointment with doctor: '
                                .$doctorName->user->full_name
                                .' at '.Carbon::parse($input->date)->format('d/m/Y')
                                .' '.$input->from_time.' '.$input->from_time_type
                                .' to '.$input->to_time.' '.$input->to_time_type,
                        ],
                    ],
                    'quantity' => 1,
                ],
            ],

            'client_reference_id' => $appointmentId,
            'mode' => 'payment',

            'success_url' => url($successUrl).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => url($cancelUrl.'?error=payment_cancelled'),
        ]);

        $result = [
            'sessionId' => $session['id'],
            'sessionUrl' => $session['url'],
        ];
        session(['session_url' => $session['url']]);
        session(['user_id' => getLogInUserId()]);

        return $result;
    }

    protected function getRedirectUrl(): string
    {
        if (session()->has('session_url')) {
            $sessionUrl = session()->get('session_url');
            session()->forget('session_url');

            return $sessionUrl;
        } else {
            return static::getResource()::getUrl('index');
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        if (! session()->has('session_url')) {
            return __('messages.flash.appointment_booked');
        }

        return '';
    }
}
