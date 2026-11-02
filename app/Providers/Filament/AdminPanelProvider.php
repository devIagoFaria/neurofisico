<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Auth\CustomEditProfile;
use App\Http\Middleware\FilamentAuthenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use App\Http\Middleware\AdminPanelRoleMiddleware;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // $role = ':'.User::ADMIN.'|'.User::DOCTOR.'|'.User::PATIENT.'|'.User::STAFF;
        return $panel
            ->id('admin')
            ->path('admin')
            ->profile(CustomEditProfile::class, isSimple: false)
            ->spa()
            ->brandLogo(getAppLogo())
            ->favicon(getAppFavicon())
            ->brandName(getAppName())
            ->brandLogoHeight('50px')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\Filament\Clusters')
            ->pages([
                Dashboard::class,
            ])
            ->plugins([
                FilamentFullCalendarPlugin::make()
                    ->schedulerLicenseKey('')
                    ->selectable(true)
                    ->editable()
                    ->timezone(config('app.timezone'))
                    ->locale(config('app.locale'))
                    ->plugins(['dayGrid', 'timeGrid'])
                    ->config([]),
                FilamentApexChartsPlugin::make(),
            ])
            ->renderHook(PanelsRenderHook::GLOBAL_SEARCH_AFTER, fn () => view('layout.topbar-date-time'))
            ->renderHook('panels::user-menu.after', fn () => Blade::render('@livewire(\'change-password\')'))
            ->renderHook(PanelsRenderHook::USER_MENU_PROFILE_AFTER, fn () => view('layout.change-password-btn'))
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
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
                FilamentAuthenticate::class,
                AdminPanelRoleMiddleware::class,
            ])
            // -------------------------------------
            // extra configuration
            // -------------------------------------
            // ->brandName(fn () => appName())
            // ->brandLogo(fn () => appLogo())
            // ->favicon(fn () => appFavicon())
            ->resourceEditPageRedirect('index')
            ->resourceCreatePageRedirect('index')
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
            ->breadcrumbs(false)
            ->maxContentWidth(Width::Full)
            ->sidebarWidth('17rem')
            ->sidebarCollapsibleOnDesktop()
            ->globalSearch(false)
            ->font('Montserrat')
            ->viteTheme('resources/css/filament/admin/theme.css');
    }
}
