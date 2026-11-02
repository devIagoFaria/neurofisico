<?php

namespace App\Livewire;

use App\Models\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationModal extends Component
{
    #[On('markSingleNotificationRead')]
    public function markAsRead($notificationId = null)
    {
        if ($notificationId) {
            $notification = Notification::find($notificationId);

            if ($notification) {
                $notification->update(['read_at' => now()]);

                FilamentNotification::make()
                    ->success()
                    ->title(__('messages.flash.notification_read'))
                    ->send();
            }
        }
    }

    #[On('markAllNotificationsRead')]
    public function markAllAsRead()
    {
        Notification::where('user_id', getLogInUserId())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        FilamentNotification::make()
            ->success()
            ->title(__('messages.flash.notification_read'))
            ->send();
    }

    public function render()
    {
        $notifications = Notification::whereNull('read_at')
            ->where('user_id', getLogInUserId())
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.notification-modal', compact('notifications'));
    }
}
