<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\FilamentLogin;
use App\Filament\Pages\Auth\FilamentRegister;
use App\Filament\Pages\Auth\FilamentRequestPasswordReset;
use App\Filament\Pages\Auth\FilamentResetPassword;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AuthPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('auth')
            ->path('')
            ->default()
            ->login(FilamentLogin::class)
            ->registration(FilamentRegister::class)
            ->passwordReset(requestAction: FilamentRequestPasswordReset::class, resetAction: FilamentResetPassword::class)
            ->spa()
            ->brandLogo(getAppLogo())
            ->favicon((getAppFavicon()))
            ->brandName(getAppName())
            ->brandLogoHeight('50px')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->colors([
                'danger' => Color::Red,
                'secondary' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'rose' => Color::Rose,
                'pink' => Color::Pink,
                'white' => Color::hex('#ffffff'),
            ])
            ->font('Montserrat')
            ->viteTheme('resources/css/filament/admin/theme.css');
    }
}
