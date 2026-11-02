<?php

namespace App\Filament\Pages;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Transaction;
use App\Models\User;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Dashboard as BaseDashboard;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.pages.dashboard';

    protected static ?string $title = 'Dashboard';

    protected static string|BackedEnum|null $navigationIcon = Phosphor::House;

    public static function getNavigationLabel(): string
    {
        return __('messages.dashboard');
    }

    public function getTitle(): string
    {
        return __('messages.dashboard');
    }

    public function getSubheading(): ?string
    {
        $user = auth()->user();
        $currentTime = now();
        $greeting = __('messages.admin_dashboard.dashboard');

        if ($user) {
            if ($user->full_name) {
                if ($currentTime->hour < 12) {
                    $greeting = __('messages.admin_dashboard.good_morning') . ', ' . $user->full_name . '! 👋';
                } elseif ($currentTime->hour >= 12 && $currentTime->hour < 17) {
                    $greeting = __('messages.admin_dashboard.good_afternoon') . ', ' . $user->full_name . '! 👋';
                } else {
                    $greeting = __('messages.admin_dashboard.good_evening') . ', ' . $user->full_name . '! 👋';
                }
            }
        }

        return $greeting;
    }

    protected function getViewData(): array
    {
        $data = [
            'doctorMonthEarnings' => 0,
            'doctorMonthEarningsPercentage' => 0,
            'patientMonthlyExpenses' => 0,
            'patientMonthlyExpensesPercentage' => 0,
        ];
        $user = auth()->user();
        $data['totalDoctorCount'] = User::toBase()->whereType(User::DOCTOR)->where('status', User::ACTIVE)->count();
        $data['todayAppointmentCount'] = Appointment::toBase()->where('date', Carbon::now()->format('Y-m-d'))->whereStatus(Appointment::BOOKED)->count();
        $data['totalPatientCount'] = User::toBase()->whereType(User::PATIENT)->count();
        $data['totalRegisteredPatientCount'] = User::toBase()->whereType(User::PATIENT)->whereRaw('Date(created_at) = CURDATE()')->count();
        $data['monthlyEarnings'] = Transaction::whereMonth('created_at', Carbon::now()->month)->whereStatus(Transaction::SUCCESS)->sum('amount');
        $lastMonthEarnings = Transaction::whereMonth('created_at', Carbon::now()->subMonth()->month)->whereStatus(Transaction::SUCCESS)->sum('amount');
        $data['monthlyEarningsPercentage'] = $lastMonthEarnings > 0
            ? number_format((($data['monthlyEarnings'] - $lastMonthEarnings) / $lastMonthEarnings) * 100, 2)
            : 0;

        $loggedInDoctor = Doctor::whereUserId(getLogInUserId())->first();

        if ($loggedInDoctor) {
            $doctorAppointmentIds = Appointment::where('doctor_id', $loggedInDoctor->id)
                ->pluck('appointment_unique_id');
            $data['doctorMonthEarnings'] = Transaction::whereIn('appointment_id', $doctorAppointmentIds)
                ->whereMonth('created_at', now()->month)
                ->whereStatus(Transaction::SUCCESS)
                ->sum('amount');

            $lastMonthDoctorEarnings = Transaction::whereIn('appointment_id', $doctorAppointmentIds)
                ->whereMonth('created_at', now()->subMonth()->month)
                ->whereStatus(Transaction::SUCCESS)
                ->sum('amount');

            $data['doctorMonthEarningsPercentage'] = $lastMonthDoctorEarnings > 0
                ? number_format(
                    (($data['doctorMonthEarnings'] - $lastMonthDoctorEarnings) / $lastMonthDoctorEarnings) * 100,
                    2
                )
                : 0;
        }

        // Check if the logged-in user is a patient
        $loggedInPatient = $user->patient;

        if ($loggedInPatient) {
            $data['patientMonthlyExpenses'] = Transaction::join('appointments', 'transactions.appointment_id', '=', 'appointments.appointment_unique_id')
                ->where('appointments.patient_id', $loggedInPatient->id)
                ->whereMonth('transactions.created_at', now()->month)
                ->where('transactions.status', Transaction::SUCCESS)
                ->sum('transactions.amount');

            $lastMonthExpenses = Transaction::join('appointments', 'transactions.appointment_id', '=', 'appointments.appointment_unique_id')
                ->where('appointments.patient_id', $loggedInPatient->id)
                ->whereMonth('transactions.created_at', now()->subMonth()->month)
                ->where('transactions.status', Transaction::SUCCESS)
                ->sum('transactions.amount');

            $data['patientMonthlyExpensesPercentage'] = $lastMonthExpenses > 0
                ? number_format(
                    (($data['patientMonthlyExpenses'] - $lastMonthExpenses) / $lastMonthExpenses) * 100,
                    2
                )
                : 0;
        }

        return [
            'patient' => $loggedInPatient,
            'doctor' => $loggedInDoctor,
            'user' => $user,
            'data' => $data,
        ];
    }
}
