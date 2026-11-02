<div class="flex items-center gap-1">
    <span>{{ $label }}:</span>
    <span
        x-data
        x-tooltip.raw="{{ $tooltip }}"
        class="cursor-help text-gray-400 transition hover:text-primary-800"
    >
        <x-heroicon-m-question-mark-circle class="w-4 h-4" />
    </span>
    <sup style="color: var(--danger-400);">*</sup>
</div>