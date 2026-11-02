<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function success(Request $request)
    {
        try {
            // Validate session ID
            $sessionId = $request->get('session_id');
            if (!$sessionId) {
                throw new \Exception('Missing session ID');
            }

            // Set Stripe API key - try both config keys
            $secretKey = getSettingValue('stripe_secret');
            if (!$secretKey) {
                throw new \Exception('Stripe secret key not configured');
            }

            Stripe::setApiKey($secretKey);

            // Retrieve the session
            $session = Session::retrieve($sessionId);

            // Validate session status
            if ($session->status !== 'complete') {
                throw new \Exception('Payment session not completed');
            }

            // Get appointment ID from metadata
            $appointmentId = $session->metadata->appointment_id ?? null;
            if (!$appointmentId) {
                throw new \Exception('Missing appointment ID in session metadata');
            }

            // Find and update the appointment
            $appointment = Appointment::find($appointmentId);
            if (!$appointment) {
                throw new \Exception('Appointment not found');
            }

            // Update appointment payment status
            $appointment->update([
                'payment_type'   => Appointment::PAID,
                'payment_method' => Appointment::STRIPE,
                'transaction_id' => $session->payment_intent,
            ]);

            // return redirect()
            //     ->route('filament.patient.resources.appointments.index')
            //     ->with('success', __('messages.transaction.paid'));

            $userId = session('user_id');
            $loggedInUser = $userId ? User::find($userId) : null;

            if ($loggedInUser?->hasRole('patient')) {
                return redirect(route('filament.patient.resources.appointments.index'))->with('success', __('messages.transaction.paid'));
            }

            if ($loggedInUser?->hasRole('clinic_admin')) {
                return redirect(route('filament.admin.resources.appointments.index'))->with('success', __('messages.transaction.paid'));
            } elseif ($loggedInUser?->hasRole('doctor')) {
                return redirect(route('filament.doctor.resources.appointments.index'))->with('success', __('messages.transaction.paid'));
            }

            return redirect(route('medical'));
        } catch (\Exception $e) {
            Log::error('Stripe payment success callback error: ' . $e->getMessage(), [
                'session_id' => $request->get('session_id'),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('filament.patient.resources.appointments.index')
                ->with('error', __('Payment processing failed. Please contact support.'));
        }
    }

    public function cancel(Request $request)
    {
        return redirect()
            ->back()
            ->with('error', __('Payment cancelled'));
    }
}
