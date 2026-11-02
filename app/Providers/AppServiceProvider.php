<?php

namespace App\Providers;


use Filament\Auth\Http\Responses\Contracts\LogoutResponse;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
        );

        $this->app->bind(
            LogoutResponse::class,
            \App\Http\Responses\LogoutResponse::class
        );

        $this->app->singleton(
            RegistrationResponse::class,
            \App\Http\Responses\RegistrationResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // URL::forceScheme('https');

        $timezone = getSettingValue('timezone');
        if ($timezone) {
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        }

        FilamentIcon::register(config('filament-icons'));

        // LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
        //     $switch
        //         ->locales(['ar', 'de', 'en', 'es', 'fr', 'it', 'pt', 'ru', 'tr', 'zh'])
        //         ->flags([
        //             'ar' => asset('web/media/flags/iraq.svg'),
        //             'de' => asset('web/media/flags/germany.svg'),
        //             'en' => asset('web/media/flags/united-states.svg'),
        //             'es' => asset('web/media/flags/spain.svg'),
        //             'fr' => asset('web/media/flags/france.svg'),
        //             'it' => asset('web/media/flags/italy.svg'),
        //             'pt' => asset('web/media/flags/portugal.svg'),
        //             'ru' => asset('web/media/flags/russia.svg'),
        //             'tr' => asset('web/media/flags/turkey.svg'),
        //             'zh' => asset('web/media/flags/china.svg'),
        //         ])
        //         ->visible(outsidePanels: true)
        //         ->outsidePanelRoutes([
        //             'auth.login',
        //             'auth.register',
        //             'auth.password-reset',
        //         ]);
        // });

        Validator::extend('captcha', function ($attribute, $value) {
            $response = Http::asForm()->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'secret' => getSettingValue('googleCaptchaSecret'),
                    'response' => $value,
                ]
            );

            return $response->json('success') === true;
        });
    }
}
