<x-filament-panels::page>
    <style>
        html[lang="ar"] .custom-time-slot-repeater table thead th {
            text-align: right !important;
        }

        html:not([lang="ar"]) .custom-time-slot-repeater table thead th {
            text-align: left !important;
        }
    </style>
    {{ $this->form }}
    <x-filament::actions :actions="$this->getFormActions()" />

</x-filament-panels::page>
