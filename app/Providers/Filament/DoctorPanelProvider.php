<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CustomEditProfile;
use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Resources\Transactions\TransactionsResource;
use App\Filament\Resources\Visits\VisitsResource;
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
use Spatie\Permission\Middleware\RoleMiddleware;

class DoctorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('doctor')
            ->path('doctors')
            ->profile(CustomEditProfile::class, isSimple: false)
            ->spa()
            ->brandLogo(getAppLogo())
            ->favicon((getAppFavicon()))
            ->brandName(getAppName())
            ->brandLogoHeight('50px')
            ->resources([
                TransactionsResource::class,
                AppointmentResource::class,
                VisitsResource::class,
            ])
            ->renderHook(PanelsRenderHook::SIDEBAR_NAV_START, fn () => view('layout.search-in-sidebar'))
            ->renderHook(PanelsRenderHook::GLOBAL_SEARCH_AFTER, fn () => view('layout.topbar-date-time'))
            ->renderHook('panels::user-menu.after', fn () => Blade::render('@livewire(\'change-password\')'))
            ->renderHook(PanelsRenderHook::USER_MENU_PROFILE_AFTER, fn () => view('layout.change-password-btn'))
            ->renderHook('panels::user-menu.after', fn () => Blade::render('@livewire(\'email-notification\')'))
            ->renderHook(PanelsRenderHook::USER_MENU_PROFILE_AFTER, fn () => view('layout.email-notification-btn'))
            ->discoverResources(in: app_path('Filament/Doctor/Resources'), for: 'App\Filament\Doctor\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverPages(in: app_path('Filament/Doctor/Pages'), for: 'App\Filament\Doctor\Pages')
            ->discoverClusters(in: app_path('Filament/Doctor/Clusters'), for: 'App\Filament\Doctor\Clusters')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\Filament\Clusters')
            ->pages([
                // Dashboard::class,
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
            //->renderHook(PanelsRenderHook::GLOBAL_SEARCH_AFTER, fn () => Blade::render('@livewire(\'notification-modal\')'))
            ->discoverWidgets(in: app_path('Filament/Doctor/Widgets'), for: 'App\Filament\Doctor\Widgets')
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
                RoleMiddleware::class.':doctor',
            ])
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
