<?php

namespace App\Filament\Patient\Pages;

use Filament\Pages\Page;
use App\Livewire\AppointmentCalendarWidget;
use Filament\Pages\Enums\SubNavigationPosition;
use App\Filament\Resources\Appointments\AppointmentResource;

class AppointmentCalendar extends Page
{
    protected static string $resource = AppointmentResource::class;
    protected string $view = 'filament.patient.pages.appointment-calendar';
    protected static bool $shouldRegisterNavigation = false;
}
