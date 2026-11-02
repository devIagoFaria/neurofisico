<?php

namespace App\Filament\Resources\FrontPatientTestimonials\Pages;

use App\Filament\Resources\FrontPatientTestimonials\FrontPatientTestimonialsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFrontPatientTestimonials extends CreateRecord
{
    protected static string $resource = FrontPatientTestimonialsResource::class;
    protected static bool $canCreateAnother = false;
    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return __('messages.flash.testimonial_creat');
    }
}
