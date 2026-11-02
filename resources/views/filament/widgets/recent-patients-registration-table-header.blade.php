<div class="mb-2 mt-2 me-3 flex items-center justify-between" wire:key="header-{{ $activeTab }}">
    <h2 class="text-lg font-semibold ms-4">
        {{ $title }}
    </h2>
    <div class="flex space-x-6 justify-end ">
        <button
            wire:click="setActiveTab('day')"
            class="px-1 py-2 text-sm font-medium transition-colors duration-200 {{ $activeTab === 'day' ? 'border-b-2 border-blue-600' : 'text-gray-400 hover:text-gray-500' }}"
        >
            {{ __('messages.admin_dashboard.day') }}
        </button>
        <button
            wire:click="setActiveTab('week')"
            class="px-1 py-2 text-sm font-medium transition-colors duration-200 {{ $activeTab === 'week' ? 'border-b-2 border-blue-600' : 'text-gray-400 hover:text-gray-500' }}"
        >
            {{ __('messages.admin_dashboard.week') }}
        </button>
        <button
            wire:click="setActiveTab('month')"
            class="px-1 py-2 text-sm font-medium transition-colors duration-200 {{ $activeTab === 'month' ? 'border-b-2 border-blue-600' : 'text-gray-400 hover:text-gray-500' }}"
        >
            {{ __('messages.admin_dashboard.month') }}
        </button>
    </div>
</div>
