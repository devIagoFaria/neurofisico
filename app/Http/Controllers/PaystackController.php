<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\Transaction;
use Filament\Notifications\Notification as NotificationsNotification;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaystackController extends Controller
{
    public function paystackConfig()
    {
        config([
            'services.paystack.publicKey' => getSettingValue('paystack_key'),
            'services.paystack.secretKey' => getSettingValue('paystack_secret'),
            'services.paystack.paymentUrl' => 'https://api.paystack.co',
        ]);
    }

    public function redirectToGateway($data)
    {
        $this->paystackConfig();

        $amount = $data['payable_amount'];

        $appointmentId = $data['id'];
        $appointment = Appointment::whereId($appointmentId)->first();
        $patientEmail = Patient::with('user')->whereId($appointment['patient_id'])->first();
        $appointmentUniqueId = $appointment['appointment_unique_id'];

        try {

            $data = [
                'email' => $patientEmail->user->email,
                'orderID' => $appointmentUniqueId,
                'amount' => $amount * 100,
                'quantity' => 1,
                'currency' => getCurrencyCode(),
                'reference' => 'SUB_'.uniqid(),
                'metadata' => json_encode($data),
            ];

            $response = Http::withToken(getSettingValue('paystack_secret'))
                ->post('https://api.paystack.co/transaction/initialize', $data);

            $responseData = $response->json();

            if (isset($responseData['status']) && $responseData['status'] === true) {
                session(['session_url' => $responseData['data']['authorization_url']]);
            }

            return $data;
        } catch (\Exception $e) {
            session()->forget('session_url');
            Log::info('Paystack Error: '.$e->getMessage());

            return Notification::make()
                ->title($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * @return Application|RedirectResponse|Redirector
     */
    public function handleGatewayCallback(Request $request): RedirectResponse
    {
        $this->paystackConfig();
        $reference = $request->get('reference');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.getSettingValue('paystack_secret'),
            ])->get("https://api.paystack.co/transaction/verify/{$reference}");
            if (! $response->successful()) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.getSettingValue('paystack_secret'),
                ])->get("https://api.paystack.co/transaction/verify/{$reference}");
            }

            if ($response->successful() && $response->json('data.status') === 'success') {
                $transactionID = $response->json('data.id');
                $originalResponse = $response->json('data');
                $response = $response->json('data.metadata');

                if (array_key_exists('payable_amount', $response)) {

                    $appointmentId = $response['id'];

                    $appointment = Appointment::whereId($appointmentId)->first();
                    $patient = Patient::with('user')->whereId($appointment['patient_id'])->first();
                    $transaction = [
                        'user_id' => $patient->user_id,
                        'transaction_id' => $transactionID,
                        'appointment_id' => $appointment['appointment_unique_id'],
                        'amount' => intval($response['payable_amount']),
                        'type' => Appointment::PAYSTACK,
                        'meta' => json_encode($originalResponse),
                    ];

                    Transaction::create($transaction);

                    $appointment->update([
                        'payment_method' => Appointment::PAYSTACK,
                        'payment_type' => Appointment::PAID,
                    ]);

                    Notification::create([
                        'title' => Notification::APPOINTMENT_PAYMENT_DONE_PATIENT_MSG,
                        'type' => Notification::PAYMENT_DONE,
                        'user_id' => $patient->user_id,
                    ]);

                    \Flash::success(__('messages.flash.appointment_booked'));
                    NotificationsNotification::make()
                        ->title(__('messages.flash.appointment_booked'))
                        ->success()
                        ->send();

                    $loggedInUser = getLogInUser();
                    if (!$loggedInUser) {
                        return redirect(route('medical'));
                    }

                    if ($loggedInUser->hasRole('patient')) {
                        return redirect(route('filament.patient.resources.appointments.index'));
                    }

                    if ($loggedInUser->hasRole('clinic_admin')) {
                        return redirect(route('filament.admin.resources.appointments.index'));
                    } elseif ($loggedInUser->hasRole('doctor')) {
                        return redirect(route('filament.doctor.resources.appointments.index'));
                    }

                    return redirect(route('medical'))->with('success', __('messages.flash.appointment_booked'));
                }

            } else {
                Notification::make()
                    ->title(__('messages.payment.payment_failed'))
                    ->danger()
                    ->send();

                return redirect()->route('filament.admin.resources.appointments.index');
            }
        } catch (HttpException $ex) {
            Log::error($ex->getMessage());
            Notification::make()
                ->title(__('messages.payment.payment_failed'))
                ->danger()
                ->send();

            return redirect()->route('filament.admin.resources.appointments.index');
        }
    }
}
