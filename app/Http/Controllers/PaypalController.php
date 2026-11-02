<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Notification as ModelsNotification;
use App\Models\Patient;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalController extends Controller
{
    public function paypalConfig()
    {
        $mode = getSettingValue('paypal_mode') ?? 'sandbox';
        $clientId = getSettingValue('paypal_key');
        $clientSecret = getSettingValue('paypal_secret');

        config([
            'paypal.mode' => $mode,
            'paypal.sandbox.client_id' => $clientId,
            'paypal.sandbox.client_secret' => $clientSecret,
            'paypal.live.client_id' => $clientId,
            'paypal.live.client_secret' => $clientSecret,
        ]);
    }

    public function onBoard(Request $request): RedirectResponse
    {
        try {
            $currencySupport = paypalCurrencySupports();
            if ($currencySupport === false) {
                Notification::make()
                    ->title(__('messages.flash.currency_not_supported'))
                    ->danger()
                    ->send();

                return redirect()->back();
            }

            $appointment = Appointment::findOrFail($request->appointmentId);

            $this->paypalConfig();
            $paypalConfig = config('paypal');
            $paypalConfig['currency'] = trim(strtoupper((string) getCurrencyCode()));

            $provider = new PayPalClient($paypalConfig);
            $provider->getAccessToken();

            $data = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $appointment->id,
                        'amount' => [
                            'value' => $appointment->payable_amount,
                            'currency_code' => getCurrencyCode(),
                        ],
                    ],
                ],
                'application_context' => [
                    'cancel_url' => route('paypal.failed'),
                    'return_url' => route('paypal.success'),
                ],
            ];

            $order = $provider->createOrder($data);

            if (isset($order['links'])) {
                foreach ($order['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        return redirect()->away($link['href']);
                    }
                }
            }

            return redirect()->route('paypal.failed');
        } catch (Exception $e) {
            return redirect()->route('paypal.failed');
        }
    }

    public function failed(): RedirectResponse
    {
        Notification::make()
            ->title(__('messages.flash.appointment_created_payment_not_complete'))
            ->danger()
            ->send();

        return $this->getRedirectUrl();
    }

    public function success(Request $request): RedirectResponse
    {
        $this->paypalConfig();
        $currency = trim(strtoupper((string) getCurrencyCode()));
        $paypalConfig = config('paypal');
        $paypalConfig['currency'] = $currency;

        $provider = new PayPalClient($paypalConfig);
        $provider->getAccessToken();
        $token = $request->get('token');

        try {
            $response = $provider->capturePaymentOrder($token);

            if ($response['status'] !== 'COMPLETED') {
                return redirect()->route('paypal.failed');
            }

            $appointmentID = $response['purchase_units'][0]['reference_id'];
            $appointment = Appointment::findOrFail($appointmentID);
            $patient = Patient::with('user')->findOrFail($appointment->patient_id);

            Transaction::create([
                'user_id' => $patient->user->id,
                'transaction_id' => $response['purchase_units'][0]['payments']['captures'][0]['id'],
                'appointment_id' => $appointment['appointment_unique_id'],
                'amount' => intval($appointment['payable_amount']),
                'type' => Appointment::PAYPAL,
                'meta' => json_encode($response),
            ]);

            $appointment->update([
                'payment_method' => Appointment::PAYPAL,
                'payment_type' => Appointment::PAID,
            ]);

            \Flash::success(__('messages.flash.appointment_booked'));
            Notification::make()
                ->title(__('messages.flash.appointment_booked'))
                ->success()
                ->send();

            ModelsNotification::create([
                'title' => ModelsNotification::APPOINTMENT_PAYMENT_DONE_PATIENT_MSG,
                'type' => ModelsNotification::PAYMENT_DONE,
                'user_id' => $patient->user->id,
            ]);

            return $this->getRedirectUrl();
        } catch (Exception $ex) {
            Notification::make()->title($ex->getMessage())->danger()->send();

            return redirect()->route('paypal.failed');
        }
    }

    private function getRedirectUrl(): RedirectResponse
    {
        $userId = session('user_id');
        $loggedInUser = getLogInUser() ?? ($userId ? User::find($userId) : null);

        if ($loggedInUser?->hasRole('patient')) {
            return redirect(route('filament.patient.resources.appointments.index'))->with('success', __('messages.transaction.paid'));
        }

        if ($loggedInUser?->hasRole('clinic_admin')) {
            return redirect(route('filament.admin.resources.appointments.index'))->with('success', __('messages.transaction.paid'));
        } elseif ($loggedInUser?->hasRole('doctor')) {
            return redirect(route('filament.doctor.resources.appointments.index'))->with('success', __('messages.transaction.paid'));
        }

        return redirect(route('medical'))->with('success', __('messages.flash.appointment_booked'));
    }

    public function createSession(Appointment $appointment)
    {
        $currency = trim(strtoupper((string) getCurrencyCode()));
        try {
            $this->paypalConfig();
            $paypalConfig = config('paypal');
            $paypalConfig['currency'] = $currency;

            $provider = new PayPalClient($paypalConfig);
            $provider->getAccessToken();

            $data = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $appointment->id,
                        'amount' => [
                            'value' => $appointment->payable_amount,
                            'currency_code' => $currency,
                        ],
                    ],
                ],
                'application_context' => [
                    'cancel_url' => route('paypal.failed'),
                    'return_url' => route('paypal.success'),
                ],
            ];

            $order = $provider->createOrder($data);
            $url = $order['links'][1]['href'];
            session(['user_id' => auth()->id()]);
            session(['session_url' => $url]);

            return $url;
        } catch (Exception $e) {
            Log::error('PayPal createSession failed', [
                'appointment_id' => $appointment->id,
                'currency' => $currency,
                'message' => $e->getMessage(),
            ]);

            $message = $e->getMessage();
            if (str_contains(strtolower($message), 'currency')) {
                $message = __('messages.flash.currency_not_supported');
            }

            Notification::make()
                ->title($message)
                ->danger()
                ->send();

            return;
        }
    }
}
