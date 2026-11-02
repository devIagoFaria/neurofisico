<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Patient;
use App\Models\Transaction;
use App\Models\Appointment;
use App\Models\Notification as AppNotification;
use Filament\Notifications\Notification as FilamentNotification;

class AppointmentPaymentStatus extends Component
{
    public function render()
    {
        return view('livewire.appointment-payment-status');
    }
}
