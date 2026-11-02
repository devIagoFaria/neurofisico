<div>
    <x-filament::modal id="change-password-modal">
        <x-slot name="heading">
            {{ __('messages.user.change_password') }}
        </x-slot>
        <div>
            <form wire:submit="save" class="space-y-4">
                {{ $this->form }}
                <div class="flex justify-end gap-3 pt-6">
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
        </div>
    </x-filament::modal>
</div>
