<div class="fi-dropdown-header flex w-full gap-2 p-1 text-sm  fi-dropdown-header-color-gray fi-color-gray">
    <button
        class="fi-dropdown-list-item flex w-full items-center gap-2 whitespace-nowrap rounded-md p-2 text-sm transition-colors duration-75 outline-none disabled:pointer-events-none disabled:opacity-70 hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5 fi-dropdown-list-item-color-gray fi-color-gray"
        x-on:click="$dispatch('open-modal', { id: 'email-notification-modal' })">
        <x-heroicon-s-bell class="w-5 h-5 text-gray-400 dark:text-gray-500" />
        <span class="fi-dropdown-list-item-label flex-1 truncate text-start text-gray-700 dark:text-gray-200"
            style="">
            {{ __('messages.user.email_notification') }}
        </span>
    </button>
</div>
