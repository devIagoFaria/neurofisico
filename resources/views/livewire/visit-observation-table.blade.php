<div class="space-y-4">

    {{-- Header Action --}}
    @if (!Auth::user()->hasRole('patient'))
        <div class="flex justify-end mb-4">
            {{ $this->addObservationAction }}
        </div>
    @endif

    {{-- Observations List --}}
    <div>
        @forelse ($this->observations as $id => $observation)
            <div class="flex items-center justify-between p-2 mb-2 mt-2 border border-b border-gray-200 dark:border-gray-700 rounded-lg">

                <p class="text-sm text-gray-800 break-words dark:text-white">
                    {{ \Illuminate\Support\Str::limit($observation, 80) }}
                </p>

                <div class="flex items-center gap-2">
                    {{ $this->viewObservationAction(['observation' => $id]) }}

                    @if (!Auth::user()->hasRole('patient'))
                        {{ $this->editObservationAction(['observation' => $id]) }}

                        <x-filament::icon-button
                            icon="heroicon-o-trash"
                            color="danger"
                            size="sm"
                            wire:click="deleteObservation({{ $id }})"
                            :label="__('messages.common.delete')"
                        />
                    @endif
                </div>
            </div>

        @empty
            <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                {{ __('messages.common.no_records_found') }}
            </div>
        @endforelse
    </div>

    {{-- Filament Action Modals --}}
    <x-filament-actions::modals />
</div>