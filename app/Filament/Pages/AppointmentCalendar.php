<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Livewire\AppointmentCalendarWidget;
use Filament\Pages\Enums\SubNavigationPosition;
use App\Filament\Resources\Appointments\AppointmentResource;

class AppointmentCalendar extends Page
{
    protected static string $resource = AppointmentResource::class;
    protected string $view = 'filament.pages.appointment-calendar';
    protected static bool $shouldRegisterNavigation = false;
}
