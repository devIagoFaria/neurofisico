@php
    use App\Models\Appointment;

    $paid = Appointment::PAID;
    $pending = Appointment::PENDING;
@endphp
<div>
    @if (!empty($label ?? null))
        <label class="text-sm font-medium text-gray-700 block">
            {{ $label }}
        </label>
    @endif
</div>
<div style="max-width: 100px" class="mt-2">
    @if ($getRecord()->payment_type === $paid)
        <x-filament::badge color="success">
            {{ __('messages.transaction.paid') }}
        </x-filament::badge>
    @else
        <div style="padding-inline: 0; padding-block: 0" wire:ignore.self class="fi-ta-select" x-data="{
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
        }"
            @click.outside="open = false" x-init="$watch('open', value => { if (value) $nextTick(() => updatePosition()); })">

            <div class="fi-input-wrp fi-disabled" x-bind:class="{ 'fi-disabled': false, 'fi-invalid': false }">

                <div class="fi-select-input">
                    <div class="fi-select-input-ctn" aria-haspopup="listbox">

                        <!-- Button -->
                        <button x-ref="button" class="fi-select-input-btn" type="button" aria-expanded="false"
                            @click="toggle()">
                            <div class="fi-select-input-value-ctn">
                                <span class="fi-select-input-value-label">
                                    {{ __('messages.transaction.pending') }}
                                </span>
                            </div>
                        </button>

                        <!-- Dropdown -->
                        <div x-ref="dropdown" class="fi-dropdown-panel fi-scrollable" role="listbox" tabindex="-1"
                            x-show="open" x-transition
                            :style="`position: fixed; left: ${dropdownStyle.left}; top: ${dropdownStyle.top}; width: ${dropdownStyle.width}; z-index: 2147483647;`">

                            <ul class="fi-dropdown-list">
                                <li class="fi-dropdown-list-item cursor-pointer fi-select-input-option" role="option"
                                    data-value="{{ $pending }}" tabindex="0">
                                    <span>{{ __('messages.transaction.pending') }}</span>
                                </li>

                                <li wire:click="paymentStatusUpdate({{ $getRecord()->id }})" @click="open = false"
                                    class="fi-dropdown-list-item cursor-pointer fi-select-input-option" role="option"
                                    data-value="{{ $paid }}" tabindex="0">
                                    <span>{{ __('messages.transaction.paid') }}</span>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
