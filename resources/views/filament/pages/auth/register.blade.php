<div>
    <div class="fixed inset-0 z-[-1] pointer-events-none" style="background-image: url('{{ getLoginBgImageUrl() }}'); background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed;"></div>

    <div class="mb-6 text-center">
        <a href="{{ route('medical') }}" class="inline-block">
            <img src="{{ getAppLogo() }}" alt="{{ getAppName() }}" class="mx-auto h-16 w-auto object-contain">
        </a>
    </div>

    <div class="w-full max-w-md">
        <h1 class="text-center mb-2 font-bold text-lg">{{ __('auth.login.create_account') . ' ?' }}</h1>
        <div class="text-sm mb-7 text-center">{{ __('auth.registration.fill_detail') }}</div>
        {{ $this->content }}
    </div>

    <div class="mt-6 text-center text-sm">
        <span>{{__('auth.registration.have_an_account')}}</span>
        <a href="{{ route('filament.auth.auth.login') }}"
            class="ml-1 font-medium underline fi-color fi-color-primary fi-text-color-600 dark:fi-text-color-300 fi-link fi-size-md">
            {{ __('auth.log_in') }}
        </a>
    </div>
</div>