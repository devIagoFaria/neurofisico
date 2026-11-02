<?php

namespace App\Filament\Resources\FrontPatientTestimonials\Pages;

use App\Filament\Resources\FrontPatientTestimonials\FrontPatientTestimonialsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFrontPatientTestimonials extends EditRecord
{
    protected static string $resource = FrontPatientTestimonialsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }
    protected function getSavedNotificationTitle(): ?string
    {
        return __('messages.flash.testimonial_update');
    }
}
