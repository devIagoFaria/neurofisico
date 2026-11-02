@php
    $notificationsIcon = $notifications->isNotEmpty() ? 'heroicon-s-bell' : 'heroicon-s-bell-slash';

    $notificationIconMap = [
        'booked' => 'heroicon-o-calendar-days',
        'checkout' => 'heroicon-o-check-circle',
        'canceled' => 'heroicon-o-x-circle',
        'payment_done' => 'heroicon-o-credit-card',
        'review' => 'heroicon-o-star',
        'live_consultation' => 'heroicon-o-video-camera',
    ];
@endphp
<div>
    <style>
        #impersonate-banner {
            z-index: 10;
        }
    </style>
    <x-filament::modal id="custom-notification-modal" class="modal-color" width="md" slide-over teleport="body"
        close-button icon="{{ $notificationsIcon }}" alignment="center" sticky-footer sticky-header
        badge="{{ $notifications->count() }}">

        @if ($notifications->isEmpty())
            <x-slot name="heading">
                {{ __('messages.common.no') . ' ' . __('messages.notification.notification') }}
            </x-slot>

            <x-slot name="description">
                {{ __('messages.notification.you_don`t_have_any_new_notification') }}
            </x-slot>
        @else
            <x-slot name="heading">
                {{ __('messages.notification.notification') }}
            </x-slot>
        @endif

        <x-slot name="trigger">
            <x-filament::button icon="heroicon-s-bell" outlined>
                @if ($notifications->isNotEmpty())
                    <x-slot name="badge">
                        {{ $notifications->count() }}
                    </x-slot>
                @endif
            </x-filament::button>
        </x-slot>

        <div class="flex flex-col gap-4">
            @foreach ($notifications as $notification)
                <button type="button" wire:key="notification-{{ $notification->id }}"
                    class="w-full text-left flex items-center justify-between px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl"
                    x-on:click="$dispatch('close-modal-quietly')"
                    wire:click="$dispatchTo('notification-modal', 'markSingleNotificationRead', { notificationId: {{ $notification->id }} })">

                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            @php
                                $icon = $notificationIconMap[$notification->type] ?? 'heroicon-o-bell';
                            @endphp

                            <x-dynamic-component :component="$icon"
                                class="w-12 h-12 text-primary-500 dark:text-primary-400" />

                            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                                {{ $notification->title }}
                            </h3>
                        </div>

                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 block">
                            {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                        </span>
                    </div>
                </button>
            @endforeach
        </div>

        <x-slot name="footer">
            <div class="custom-notify">
                <x-filament::link wire:click="$dispatchTo('notification-modal', 'markAllNotificationsRead')"
                    x-on:click="$dispatch('close-modal-quietly', { id: 'custom-notification-modal' })" :disabled="count($notifications) == 0"
                    class="cursor-pointer">
                    {{ __('messages.notification.mark_all_as_read') }}
                </x-filament::link>
            </div>
        </x-slot>

    </x-filament::modal>
</div>
