<div>
    <x-filament::modal id="email-notification-modal">
        <x-slot name="heading">
            {{ __('messages.user.email_notification') }}
        </x-slot>

        <form wire:submit="save" class="space-y-6">
            <label class="flex items-center gap-3">
                <x-filament::input.checkbox wire:model="email_notification" />
                <span class="text-sm font-medium text-gray-950 dark:text-white">
                    {{ __('messages.user.email_notification') }}
                </span>
            </label>
            <p class="text-xs text-gray-500 dark:text-gray-400 ml-7">
                {{ __('messages.user.email_notification_instruction') }}
            </p>

            <div class="flex justify-end gap-3 pt-2">
                <x-filament::button type="submit">
                    {{ __('messages.common.save') }}
                </x-filament::button>

                <x-filament::button
                    type="button"
                    color="gray"
                    x-on:click="close(); $wire.call('resetFormData')"
                >
                    {{ __('messages.common.cancel') }}
                </x-filament::button>
            </div>
        </form>
    </x-filament::modal>
</div>
