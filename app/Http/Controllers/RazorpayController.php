<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Notification as ModelsNotification;
use App\Models\Transaction;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;

class RazorpayController extends Controller
{
    public function onBoard(Request $request)
    {
        $appointment = Appointment::findOrFail($request->appointmentId);
        $patient = $appointment->patient;

        $razorpayKey = getSettingValue('razorpay_key');
        $razorpaySecret = getSettingValue('razorpay_secret');

        if (empty($razorpayKey) || empty($razorpaySecret)) {
            throw new Exception('Razorpay credentials not configured.');
        }

        $api = new Api($razorpayKey, $razorpaySecret);

        try {
            $orderData = [
                'receipt' => (string) $appointment->id,
                'amount' => $appointment->payable_amount * 100,
                'currency' => getCurrencyCode(),
                'payment_capture' => 1,
            ];

            $razorpayOrder = $api->order->create($orderData);

            // Ensure we have all required data
            $currencySupport = razorpayCurrencySupports();
            if (empty($currencySupport)) {
                Log::error('Currency code is not set for Razorpay transaction');
                throw new Exception('Currency code is not configured');
            }
            $currency = getCurrencyCode();

            // Return view with embedded Razorpay popup that opens automatically
            return view('razorpay.popup', [
                'order_id' => $razorpayOrder->id,
                'amount' => $orderData['amount'],
                'currency' => $currency,
                'name' => $patient->user->full_name ?? '',
                'email' => $patient->user->email ?? '',
                'contact' => $patient->user->contact ?? '',
                'appointment_id' => $appointment->id,
                'key' => $razorpayKey,
            ]);
        } catch (Exception $e) {
            Log::error('Razorpay Order Error: '.$e->getMessage());

            return redirect()->route('razorpay.failed');
        }
    }

    public function paymentSuccess(Request $request)
    {
        $input = $request->all();
        $razorpayKey = getSettingValue('razorpay_key');
        $razorpaySecret = getSettingValue('razorpay_secret');

        if (empty($razorpayKey) || empty($razorpaySecret)) {
            throw new Exception('Razorpay credentials not configured.');
        }

        $api = new Api($razorpayKey, $razorpaySecret);

        try {
            // Verify Signature
            $attributes = [
                'razorpay_order_id' => $input['razorpay_order_id'],
                'razorpay_payment_id' => $input['razorpay_payment_id'],
                'razorpay_signature' => $input['razorpay_signature'],
            ];
            $api->utility->verifyPaymentSignature($attributes);

            $appointment = Appointment::findOrFail($request->appointment_id);
            $patientUser = $appointment->patient->user;

            Transaction::create([
                'user_id' => $patientUser->id,
                'transaction_id' => $input['razorpay_payment_id'],
                'appointment_id' => $appointment->appointment_unique_id,
                'amount' => $appointment->payable_amount,
                'type' => Appointment::RAZORPAY,
                'meta' => json_encode($input),
            ]);

            $appointment->update([
                'payment_method' => Appointment::RAZORPAY,
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
                'user_id' => $patientUser->id,
            ]);

            return $this->getRedirectUrl();
        } catch (Exception $e) {
            Log::error('Razorpay Success Error: ' . $e->getMessage());

            return redirect()->route('razorpay.failed');
        }
    }

    public function paymentFailed()
    {
        Notification::make()
            ->title(__('messages.flash.appointment_created_payment_not_complete'))
            ->danger()
            ->send();

        return $this->getRedirectUrl();
    }

    public function paymentSuccessWebHook(Request $request)
    {
        // Handle webhook verification for payment success
        $input = $request->all();

        $razorpayKey = getSettingValue('razorpay_key');
        $razorpaySecret = getSettingValue('razorpay_secret');

        if (empty($razorpayKey) || empty($razorpaySecret)) {
            throw new Exception('Razorpay credentials not configured.');
        }

        $api = new Api($razorpayKey, $razorpaySecret);

        try {
            // Verify webhook signature
            $webhookSecret = config('payments.razorpay.webhook_secret'); // Make sure to add this to config
            $signature = $request->header('X-Razorpay-Signature');

            if ($webhookSecret && $signature) {
                $api->utility->verifyWebhookSignature(json_encode($input), $signature, $webhookSecret);
            }

            // Process the webhook payload
            $payload = $request->all();

            if (isset($payload['event']) && $payload['event'] === 'payment.captured') {
                $paymentId = $payload['payload']['payment']['entity']['id'];
                $orderId = $payload['payload']['payment']['entity']['order_id'];

                // Find the appointment by order receipt (which was set as appointment id)
                // Since we can't directly link via order_id, we'd need to store the mapping
                // For now, we'll log this event
                Log::info('Razorpay Webhook Payment Captured', [
                    'payment_id' => $paymentId,
                    'order_id' => $orderId,
                    'payload' => $payload,
                ]);
            }

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            Log::error('Razorpay Webhook Verification Error: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    private function getRedirectUrl()
    {
        $loggedInUser = getLogInUser();
        if (!$loggedInUser) {
            return redirect(route('medical'))->with('success', __('messages.flash.appointment_booked'));
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
}
