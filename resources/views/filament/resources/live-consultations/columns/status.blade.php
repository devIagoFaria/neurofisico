@php
    $awaited = 0;
    $cancelled = 1;
    $finished = 2;
@endphp
{{-- AWAITED → DROPDOWN --}}
@if ($getState() === $awaited)
    <div wire:ignore.self x-data="{
        open: false,
        dropdownStyle: { left: '0px', top: '0px', width: '0px' },
    
        toggle() {
            this.open = !this.open;
            if (this.open) this.$nextTick(() => this.updatePosition());
        },
    
        updatePosition() {
            const button = this.$refs.button;
            const rect = button.getBoundingClientRect();
            this.dropdownStyle = {
                left: rect.left + 'px',
                top: rect.bottom + 'px',
                width: button.offsetWidth + 'px',
            };
        }
    }" @click.outside="open = false">
        <div class="fi-ta-select">
            <div class="fi-input-wrp">
                <div class="fi-select-input">
                    <button x-ref="button" type="button" @click="toggle()" class="fi-select-input-btn">
                        <span class="fi-select-input-value-label">
                            {{ __('messages.live_consultation.awaited') }}
                        </span>
                    </button>

                    <div x-show="open" x-transition class="fi-dropdown-panel fi-scrollable"
                        :style="`position: fixed; left: ${dropdownStyle.left}; top: ${dropdownStyle.top}; width: ${dropdownStyle.width}; z-index: 9999;`">
                        <ul class="fi-dropdown-list">
                            <li class="fi-dropdown-list-item opacity-60 cursor-not-allowed">
                                {{ __('messages.live_consultation.awaited') }}
                            </li>

                            <li wire:click="changeStatus({{ $cancelled }}, {{ $getRecord()->id }})"
                                @click="open = false" class="fi-dropdown-list-item cursor-pointer">
                                {{ __('messages.common.cancelled') }}
                            </li>

                            <li wire:click="changeStatus({{ $finished }}, {{ $getRecord()->id }})"
                                @click="open = false" class="fi-dropdown-list-item cursor-pointer">
                                {{ __('messages.live_consultation.finished') }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CANCELLED → BADGE --}}
@elseif ($getState() === $cancelled)
    <span style="--c-50:var(--danger-50);--c-400:var(--danger-400);--c-600:var(--danger-600);"
        class="flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30 fi-color-danger">
        {{ __('messages.common.cancelled') }}
    </span>

    {{-- FINISHED → BADGE --}}
@elseif ($getState() === $finished)
    <span style="--c-50:var(--success-50);--c-400:var(--success-400);--c-600:var(--success-600);"
        class="flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30 fi-color-success">
        {{ __('messages.live_consultation.finished') }}
    </span>
@endif
