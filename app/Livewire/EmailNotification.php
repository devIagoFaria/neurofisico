<?php

namespace App\Livewire;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EmailNotification extends Component
{
    public bool $email_notification = true;

    public function mount(): void
    {
        $this->resetFormData();
    }

    public function render()
    {
        return view('livewire.email-notification');
    }

    public function save(): void
    {
        $user = Auth::user();
        $user->email_notification = $this->email_notification;
        $user->save();

        Notification::make()
            ->title(__('messages.flash.email_notification'))
            ->success()
            ->send();

        $this->dispatch('close-modal', id: 'email-notification-modal');
    }

    public function resetFormData(): void
    {
        $this->email_notification = (bool) Auth::user()?->email_notification;
    }
}
