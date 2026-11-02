<x-filament-panels::page>
    <div class="space-y-6">
        @if ($this->doctorRole && empty($this->checkTimeZone->time_zone))
            <div class="p-4 rounded-lg bg-danger-50 dark:bg-danger-950/30 border border-danger-200 dark:border-danger-800 flex items-center">
                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="w-5 h-5 text-danger-500 mr-3" />
                <span class="text-danger-800 dark:text-danger-200 font-medium text-sm">
                    {{ __('messages.common.note_we_are_taking_the_timezone_of_your_doctors_while_creating_appointment_in_calendar') }}
                </span>
            </div>
        @endif

        <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            @if (!$this->googleCalendarIntegrationExists)
                <div class="space-y-6">
                    @if ($this->jsonFileUploaded)
                        <x-filament::button wire:click="connectGoogleCalendar">
                            {{ __('messages.setting.connect_your_google_calendar') }}
                        </x-filament::button>
                    @else
                        <x-filament::button disabled>
                            {{ __('messages.setting.connect_your_google_calendar') }}
                        </x-filament::button>
                    @endif

                    <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                        {{ $this->form }}
                    </div>

                    <x-filament::button wire:click="saveFilePath">
                        {{ __('messages.common.save') }}
                    </x-filament::button>
                </div>
            @else
                <div class="space-y-6">

                    {{-- Header text --}}
                    <div>
                        <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">
                            {{ __('messages.setting.select_your_calendars_from_google_calendar_in_which_you_want_to_create_the_appointments') . '.' }}

                            @php
                                $tooltipMessage = getLogInUser()->hasRole('doctor')
                                    ? __(
                                        'messages.setting.when_patient_book_an_appointment_with_you_new_appointment_will_created_on_selected_calendars',
                                    )
                                    : __(
                                        'messages.setting.when_you_book_an_appointment_new_appointment_will_created_on_selected_calendars',
                                    );
                            @endphp

                            <span x-tooltip.raw="{{ $tooltipMessage }}"
                                class="inline-block justify-center items-center text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-300">
                                <x-filament::icon icon="heroicon-m-question-mark-circle" class="w-5 h-5" />
                            </span>
                        </h3>
                    </div>

                    {{-- Calendars list --}}
                    @if (count($this->googleCalendarLists) > 0)
                        <div class="space-y-3">
                            @foreach ($this->googleCalendarLists as $calendar)
                                <label class="flex items-center p-4 cursor-pointer">

                                    <input type="checkbox" wire:model="selectedCalendars" value="{{ $calendar->id }}"
                                        class="h-4 w-4 text-primary-600 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500">

                                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $calendar->calendar_name }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        {{-- Action buttons --}}
                        <div class="flex flex-wrap gap-3 pt-4">
                            <x-filament::button color="primary" wire:click="saveCalendars">
                                {{ __('Save') }}
                            </x-filament::button>

                            <x-filament::button color="info" wire:click="syncGoogleCalendar">
                                {{ __('Sync your Google Calendar') }}
                            </x-filament::button>

                            <x-filament::button color="danger" wire:click="disconnectGoogleCalendar">
                                {{ __('Disconnect your Google Calendar') }}
                            </x-filament::button>
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">
                            {{ __('messages.setting.no_calendars_found') }}
                        </p>
                    @endif
                </div>
            @endif

        </div>
    </div>

    @script
        <script>
            $wire.on('redirectToGoogleAuth', () => {
                console.log('redirectToGoogleAuth event received');
                window.location.href = '{{ route('googleAuth') }}';
            });

            window.addEventListener('livewire-update', function() {
                if (window.shouldRedirectToGoogleAuth) {
                    console.log('Fallback redirect triggered');
                    window.location.href = '{{ route('googleAuth') }}';
                    window.shouldRedirectToGoogleAuth = false;
                }
            });
        </script>
    @endscript
</x-filament-panels::page>
