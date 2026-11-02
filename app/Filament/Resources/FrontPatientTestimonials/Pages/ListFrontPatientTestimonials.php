<?php

namespace App\Filament\Resources\FrontPatientTestimonials\Pages;

use App\Filament\Resources\FrontPatientTestimonials\FrontPatientTestimonialsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFrontPatientTestimonials extends ListRecords
{
    protected static string $resource = FrontPatientTestimonialsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
