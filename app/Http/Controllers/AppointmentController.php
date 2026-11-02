<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFrontAppointmentRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\AppointmentRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Stripe\Checkout\Session;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AppointmentController extends AppBaseController
{
    /** @var AppointmentRepository */
    private $appointmentRepository;

    public function __construct(AppointmentRepository $appointmentRepo)
    {
        $this->appointmentRepository = $appointmentRepo;
    }

    public function frontHomeAppointmentBook(Request $request): RedirectResponse
    {
        return redirect()->route('medicalAppointment')->with(['data' => $request->all()]);
    }

    public function getPatientName(Request $request): JsonResponse|bool
    {
        $patient = User::whereEmail($request->email)
            ->whereType(User::PATIENT)
            ->first();

        if (! $patient) {
            return response()->json([
                'success' => false,
                'message' => __('messages.flash.patient_not_found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.appointment.patient_name_retrieved'),
            'data' => $patient->full_name,
        ]);
    }

    public function doctorBookAppointment(Doctor $doctor): RedirectResponse
    {
        return redirect()->route('medicalAppointment')->with([
            'data' => ['doctor_id' => $doctor->id],
        ]);
    }

    public function serviceBookAppointment(Service $service): RedirectResponse
    {
        return redirect()->route('medicalAppointment')->with([
            'data' => [
                'service' => Service::whereStatus(Service::ACTIVE)->pluck('name', 'id'),
                'service_id' => $service->id,
            ],
        ]);
    }

    public function paymentSuccess(Request $request): RedirectResponse
    {
        $sessionId = $request->get('session_id');
        if (empty($sessionId)) {
            throw new UnprocessableEntityHttpException(__('messages.appointment.session_id_required'));
        }
        setStripeApiKey();

        $sessionData = Session::retrieve($sessionId);
        $appointment = Appointment::whereAppointmentUniqueId($sessionData->client_reference_id)->first();
        $patientId = User::whereEmail($sessionData->customer_details->email)->pluck('id')->first();
        $transaction = [
            'user_id' => $patientId,
            'transaction_id' => $sessionData->id,
            'appointment_id' => $sessionData->client_reference_id,
            'amount' => intval($sessionData->amount_total / 100),
            'type' => Appointment::STRIPE,
            'meta' => $sessionData,
        ];

        Transaction::create($transaction);

        $appointment->update([
            'payment_method' => Appointment::STRIPE,
            'payment_type' => Appointment::PAID,
        ]);

        \Flash::success(__('messages.flash.appointment_booked'));
        notify(__('messages.flash.appointment_booked'));

        $patient = Patient::whereUserId($patientId)->with('user')->first();
        Notification::create([
            'title' => Notification::APPOINTMENT_PAYMENT_DONE_PATIENT_MSG,
            'type' => Notification::PAYMENT_DONE,
            'user_id' => $patient->user_id,
        ]);

        // if (parse_url(url()->previous(), PHP_URL_PATH) == '/medical-appointment') {
        //     return redirect(route('medicalAppointment'));
        // }

        // if (! getLogInUser()) {
        //     return redirect(route('medical'));
        // }

        // if (getLogInUser()->hasRole('patient')) {
        //     return redirect(route('patients.patient-appointments-index'));
        // }

        $userId = session('user_id');
        $loggedInUser = getLogInUser() ?? ($userId ? User::find($userId) : null);

        if ($loggedInUser?->hasRole('patient')) {
            return redirect(route('filament.patient.resources.appointments.index'));
        }

        if ($loggedInUser?->hasRole('clinic_admin')) {
            return redirect(route('filament.admin.resources.appointments.index'));
        } elseif ($loggedInUser?->hasRole('doctor')) {
            return redirect(route('filament.doctor.resources.appointments.index'));
        }

        return redirect(route('medical'))->with('success', __('messages.flash.appointment_booked'));
    }

    /**
     * @return Application|RedirectResponse|Redirector
     */
    public function handleFailedPayment(): RedirectResponse
    {
        setStripeApiKey();

        notify(__('messages.flash.appointment_created_payment_not_complete'), 'danger');

        // if (! getLogInUser()) {
        //     return redirect(route('medicalAppointment'));
        // }

        // if (getLogInUser()->hasRole('patient')) {
        //     return redirect(route('patients.patient-appointments-index'));
        // }

        return redirect(route('filament.admin.resources.appointments.index'));
    }

    public function appointmentPdf($id)
    {
        $datas = Appointment::with(['patient.user', 'doctor.user', 'services'])->findOrFail($id);
        $logo = Setting::where('key', 'logo')->pluck('value')->first();
        $pdf = Pdf::loadView('appointment_pdf.invoice', ['datas' => $datas, 'logo' => $logo]);

        return $pdf->download('invoice.pdf');
    }

    public function cancelAppointment($patient_id, $appointment_unique_id): RedirectResponse
    {
        // not complate  query
        $uniqueId = Crypt::decryptString($appointment_unique_id);
        $appointment = Appointment::whereAppointmentUniqueId($uniqueId)->wherePatientId($patient_id)->first();

        $appointment->update(['status' => Appointment::CANCELLED]);

        return redirect(route('medical'));
    }

    public function frontAppointmentBook(CreateFrontAppointmentRequest $request): JsonResponse
    {
        app()->setLocale(checkLanguageSession());
        $input = $request->all();
        $appointment = $this->appointmentRepository->frontSideStore($input);
        $settings = Setting::pluck('value', 'key')->toArray();

        if ($input['payment_type'] == Appointment::STRIPE) {
            $result = $this->appointmentRepository->createSession($appointment);

            return $this->sendResponse([
                'payment_type' => $input['payment_type'],
                $result,
            ], 'Stripe '.__('messages.appointment.session_created_successfully'));
        }

        if ($input['payment_type'] == Appointment::PAYPAL) {
            if ($request->isXmlHttpRequest()) {
                $paypalController = new PaypalController;
                $paypalController->createSession($appointment);

                if (session('session_url')) {
                    return $this->sendResponse([
                        'redirect_url' => session('session_url'),
                        'payment_type' => $input['payment_type'],
                        'appointmentId' => $appointment->id,
                    ], 'Paypal '.__('messages.appointment.session_created_successfully'));
                } else {
                    return $this->sendResponse([
                        'payment_type' => $input['payment_type'],
                        'appointmentId' => $appointment->id,
                    ], 'Paypal '.__('messages.appointment.session_created_successfully'));
                }
            }
        }

        if ($input['payment_type'] == Appointment::PAYSTACK && ! empty($settings['paystack_enable']) && ! empty($settings['paystack_key']) && ! empty($settings['paystack_secret'])) {
            $paystackController = new PaystackController;
            $paystackController->redirectToGateway($appointment);

            if (session('session_url')) {
                return $this->sendResponse([
                    'redirect_url' => session('session_url'),
                    'payment_type' => $input['payment_type'],
                    'appointmentId' => $appointment->id,
                ], 'Paystack '.__('messages.appointment.session_created_successfully'));
            } else {
                return $this->sendResponse([
                    'payment_type' => $input['payment_type'],
                    'appointmentId' => $appointment->id,
                ], 'Paystack '.__('messages.appointment.session_created_successfully'));
            }
        }

        if ($input['payment_type'] == Appointment::RAZORPAY) {
            return $this->sendResponse([
                'payment_type' => $input['payment_type'],
                'appointmentId' => $appointment->id,
            ], 'Razorpay '.__('messages.appointment.session_created_successfully'));
        }

        if ($input['payment_type'] == Appointment::PAYTM) {
            return $this->sendResponse([
                'payment_type' => $input['payment_type'],
                'appointmentId' => $appointment->id,
            ], 'Paytm '.__('messages.appointment.session_created_successfully'));
        }

        if ($input['payment_type'] == Appointment::AUTHORIZE) {
            return $this->sendResponse([
                'payment_type' => $input['payment_type'],
                'appointmentId' => $appointment->id,
            ], __('messages.appointment.authorize_session_created_successfully'));
        }

        $data['payment_type'] = $input['payment_type'];
        $data['appointmentId'] = $appointment->id;

        return $this->sendResponse($data, __('messages.flash.appointment_booked'));
    }

    public function manuallyPayment(Request $request): RedirectResponse
    {
        $input = $request->all();
        $appointment = Appointment::findOrFail($input['appointmentId'])->load('patient');
        $transaction = [
            'user_id' => $appointment->patient->user_id,
            'transaction_id' => Str::random(10),
            'appointment_id' => $appointment->appointment_unique_id,
            'amount' => $appointment->payable_amount,
            'type' => Appointment::MANUALLY,
            'status' => Transaction::PENDING,
        ];

        Transaction::create($transaction);

        \Flash::success(__('messages.flash.appointment_booked'));
        notify(__('messages.flash.appointment_booked'));

        $loggedInUser = getLogInUser();
        if ($loggedInUser) {
            if ($loggedInUser->hasRole('patient')) {
                return redirect(route('filament.patient.resources.appointments.index'));
            }

            if ($loggedInUser->hasRole('clinic_admin')) {
                return redirect(route('filament.admin.resources.appointments.index'));
            } elseif ($loggedInUser->hasRole('doctor')) {
                return redirect(route('filament.doctor.resources.appointments.index'));
            }
        }

        return redirect(route('medical'))->with('success', __('messages.flash.appointment_booked'));

    }
}
