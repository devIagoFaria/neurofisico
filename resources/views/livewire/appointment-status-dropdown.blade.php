@php
    use App\Models\Appointment;

    $record = $getRecord();

    $book = Appointment::BOOKED;
    $checkIn = Appointment::CHECK_IN;
    $checkOut = Appointment::CHECK_OUT;
    $cancel = Appointment::CANCELLED;
@endphp

@if ($record)
    @if (!empty($label ?? null))
        <label class="text-sm font-medium text-gray-700 block">
            {{ $label }}
        </label>
    @endif

    <div class="mt-2" style="padding-inline: 0; padding-block: 0; max-width: 190px" wire:ignore.self class="fi-ta-select"
        x-data="{
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
        
                    // Flip dropdown if it goes beyond viewport
                    if (rect.left + dropdownWidth > viewportWidth) {
                        left = viewportWidth - dropdownWidth - 10; // 10px padding
                    }
        
                    this.dropdownStyle = {
                        left: left + 'px',
                        top: rect.bottom + 'px',
                        width: dropdownWidth + 'px'
                    };
                }
            }
        }" @click.outside="open = false" x-init="$watch('open', value => { if (value) $nextTick(() => updatePosition()); })">

        <div class="fi-input-wrp fi-disabled" x-bind:class="{ 'fi-disabled': false, 'fi-invalid': false }">

            <div class="fi-select-input">
                <div class="fi-select-input-ctn" aria-haspopup="listbox">

                    <!-- Button -->
                    <button x-ref="button" class="fi-select-input-btn" type="button" aria-expanded="false"
                        @click="toggle()">
                        <div class="fi-select-input-value-ctn">
                            <span class="fi-select-input-value-label">
                                @if ($record->status == $book)
                                    {{ __('messages.common.booked') }}
                                @elseif ($record->status == $checkIn)
                                    {{ __('messages.common.check in') }}
                                @elseif ($record->status == $checkOut)
                                    {{ __('messages.common.check out') }}
                                @elseif ($record->status == $cancel)
                                    {{ __('messages.common.cancelled') }}
                                @endif
                            </span>
                        </div>
                    </button>

                    <!-- Dropdown -->
                    <div x-ref="dropdown" class="fi-dropdown-panel fi-scrollable" role="listbox" tabindex="-1"
                        x-show="open" x-transition
                        :style="`position: fixed; left: ${dropdownStyle.left}; top: ${dropdownStyle.top}; width: ${dropdownStyle.width}; z-index: 2147483647;`">

                        <ul class="fi-dropdown-list">
                            {{-- BOOK (always disabled) --}}
                            <li class="fi-dropdown-list-item fi-select-input-option disabled" role="option"
                                aria-disabled="true">
                                <span>{{ __('messages.common.' . strtolower(Appointment::STATUS[$book])) }}</span>
                            </li>

                            {{-- CHECK IN --}}
                            @php
                                $checkInDisabled = in_array($getRecord()->status, [$cancel, $checkOut]);
                            @endphp
                            <li class="fi-dropdown-list-item fi-select-input-option
            {{ $checkInDisabled ? 'disabled' : 'cursor-pointer' }}"
                                role="option" aria-disabled="{{ $checkInDisabled ? 'true' : 'false' }}"
                                @unless ($checkInDisabled)
            wire:click="changeStatus({{ $getRecord()->id }}, {{ $checkIn }})"
        @endunless>
                                <span>{{ __('messages.common.' . strtolower(Appointment::STATUS[$checkIn])) }}</span>
                            </li>

                            {{-- CHECK OUT --}}
                            @php
                                $checkOutDisabled = in_array($getRecord()->status, [$cancel, $book]);
                            @endphp
                            <li class="fi-dropdown-list-item fi-select-input-option
            {{ $checkOutDisabled ? 'disabled' : 'cursor-pointer' }}"
                                role="option" aria-disabled="{{ $checkOutDisabled ? 'true' : 'false' }}"
                                @unless ($checkOutDisabled)
            wire:click="changeStatus({{ $getRecord()->id }}, {{ $checkOut }})"
        @endunless>
                                <span>{{ __('messages.common.' . strtolower(Appointment::STATUS[$checkOut])) }}</span>
                            </li>

                            {{-- CANCEL --}}
                            @php
                                $cancelDisabled = in_array($getRecord()->status, [$checkIn, $checkOut]);
                            @endphp
                            <li class="fi-dropdown-list-item fi-select-input-option
            {{ $cancelDisabled ? 'disabled' : 'cursor-pointer' }}"
                                role="option" aria-disabled="{{ $cancelDisabled ? 'true' : 'false' }}"
                                @unless ($cancelDisabled)
            wire:click="changeStatus({{ $getRecord()->id }}, {{ $cancel }})"
        @endunless>
                                <span>{{ __('messages.common.' . strtolower(Appointment::STATUS[$cancel])) }}</span>
                            </li>
                        </ul>


                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
