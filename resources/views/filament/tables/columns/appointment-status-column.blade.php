<div style="width: 100%; display: flex; text-align: center;">
    @php
        use App\Models\Appointment;
        $record = $getRecord();
        $currentStatus = $record->status;

        $options = [
            Appointment::BOOKED => __('messages.appointment.booked'),
            Appointment::CHECK_IN => __('messages.appointment.check_in'),
            Appointment::CHECK_OUT => __('messages.appointment.check_out'),
            Appointment::CANCELLED => __('messages.common.cancelled'),
        ];

        $isDisabled = function ($value) use ($currentStatus) {
            if ($value === $currentStatus) {
                return false;
            }

            return match ($currentStatus) {
                Appointment::BOOKED => in_array($value, [
                    Appointment::BOOKED,
                    Appointment::CHECK_OUT,
                ]),
                Appointment::CHECK_IN => in_array($value, [
                    Appointment::BOOKED,
                    Appointment::CHECK_IN,
                    Appointment::CANCELLED,
                ]),
                Appointment::CHECK_OUT => true,
                Appointment::CANCELLED => true,
                default => false,
            };
        };
    @endphp

    <div wire:ignore.self class="fi-ta-select w-full" x-data="{
        open: false,
        dropdownStyle: { left: '0px', top: '0px', width: '0px' },

        toggle() {
            this.open = !this.open;
            if (this.open) this.$nextTick(() => this.updatePosition());
        },

        updatePosition() {
            const button = this.$refs.button;
            const dropdown = this.$refs.dropdown;

            if (button && dropdown) {
                const rect = button.getBoundingClientRect();
                const dropdownWidth = button.offsetWidth;
                const viewportWidth = window.innerWidth;

                let left = rect.left;

                if (rect.left + dropdownWidth > viewportWidth) {
                    left = viewportWidth - dropdownWidth - 10;
                }

                this.dropdownStyle = {
                    left: left + 'px',
                    top: rect.bottom + 'px',
                    width: dropdownWidth + 'px'
                };
            }
        }
    }" @click.outside="open = false"
        x-init="$watch('open', value => { if (value) $nextTick(() => updatePosition()); })"
        style="width: 100%; max-width: 190px; position: relative;">

        <!-- Select Wrapper -->
        <div class="fi-input-wrp border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 shadow-sm transition duration-75 focus-within:ring-2 focus-within:ring-primary-600 focus-within:border-primary-600" style="width: 100%;">
            <div class="fi-select-input w-full" style="width: 100%;">
                <div class="fi-select-input-ctn w-full" aria-haspopup="listbox" style="width: 100%; position: relative;">

                    <!-- Trigger Button -->
                    <button x-ref="button" class="w-full text-left px-3 py-2 flex items-center justify-between text-sm text-gray-900 dark:text-gray-100 bg-transparent rounded-lg cursor-pointer focus:outline-none" type="button" aria-expanded="false" @click="toggle()" style="width: 100%;">
                        <span class="truncate">
                            {{ $options[$currentStatus] ?? 'N/A' }}
                        </span>
                        <svg class="h-5 w-5 text-gray-400 dark:text-gray-500 transition duration-75" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="margin-left: 8px; flex-shrink: 0;">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Floating Dropdown Panel -->
                    <div x-ref="dropdown" class="fi-dropdown-panel bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg" role="listbox" tabindex="-1"
                        x-show="open" x-transition
                        style="display: none; position: fixed; max-height: 250px; overflow-y: auto; margin-top: 4px;"
                        :style="`position: fixed; left: ${dropdownStyle.left}; top: ${dropdownStyle.top}; width: ${dropdownStyle.width}; z-index: 2147483647; display: ${open ? 'block' : 'none'};`">

                        <ul class="py-1" style="list-style: none; margin: 0; padding: 0;">
                            @foreach ($options as $value => $label)
                                @php
                                    $disabled = $isDisabled($value);
                                @endphp
                                @if ($disabled)
                                    <li class="px-3 py-2 text-gray-500 dark:text-gray-400 cursor-not-allowed text-sm text-left">
                                        <span>{{ $label }}</span>
                                    </li>
                                @else
                                    <li wire:click="updateAppointmentStatus({{ $record->id }}, {{ $value }})"
                                        @click="open = false"
                                        class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700/50 cursor-pointer text-gray-900 dark:text-gray-100 text-sm text-left transition-colors duration-150">
                                        <span>{{ $label }}</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
