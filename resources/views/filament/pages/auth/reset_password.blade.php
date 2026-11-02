<div>
    <div class="fixed inset-0 z-[-1] pointer-events-none" style="background-image: url('{{ getLoginBgImageUrl() }}'); background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed;"></div>

    <div class="mb-6 text-center">
        <a href="{{ route('medical') }}" class="inline-block">
            <img src="{{ getAppLogo() }}" alt="{{ getAppName() }}" class="mx-auto h-16 w-auto object-contain">
        </a>
    </div>

    <div class="w-full max-w-md">
        <h1 class="text-center mb-2 font-bold text-lg">{{ __('Reset Your Password') . ' ?' }}</h1>
        <div class="text-sm mb-7 text-center">{{ __('Enter a new password to regain access to your account.') }}</div>
        {{ $this->content }}
    </div>

    <div class="flex justify-center mt-6">
        <a href="{{ route('filament.auth.auth.login') }}"
            class="flex items-center gap-2 font-medium fi-color fi-color-primary fi-text-color-600 dark:fi-text-color-300 fi-link fi-size-md">
            <x-filament::icon icon="heroicon-m-arrow-left" class="w-5 h-5" />
            {{ __('messages.back_to_login') }}
        </a>
    </div>
</div>
