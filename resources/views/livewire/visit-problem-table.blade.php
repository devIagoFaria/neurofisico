<div class="space-y-4">

    {{-- Header Action --}}
    @if (!Auth::user()->hasRole('patient'))
        <div class="flex justify-end mb-4">
            {{ $this->addProblemAction }}
        </div>
    @endif

    {{-- Problem List --}}
    <div>
        @forelse ($this->problems as $id => $problem)
            <div class="flex items-center justify-between p-2 mb-2 mt-2 border border-b border-gray-200 dark:border-gray-700 rounded-lg">
                
                <p class="text-sm text-gray-800 break-words dark:text-white">
                    {{ \Illuminate\Support\Str::limit($problem, 80) }}
                </p>

                <div class="flex items-center gap-2">

                    {{ $this->viewProblemAction(['problem' => $id]) }}
                
                    @if (!Auth::user()->hasRole('patient'))
                
                        {{ $this->editProblemAction(['problem' => $id]) }}
                
                        <x-filament::icon-button
                            icon="heroicon-o-trash"
                            color="danger"
                            size="sm"
                            wire:click="deleteProblem({{ $id }})"
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