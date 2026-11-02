<?php

namespace App\Filament\Resources\FrontPatientTestimonials\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class FrontPatientTestimonialsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('messages.web.name') . ':')
                    ->placeholder(__('messages.web.name'))
                    ->required(),
                TextInput::make('designation')
                    ->label(__('messages.front_patient_testimonial.designation') . ':')
                    ->placeholder(__('messages.front_patient_testimonial.designation'))
                    ->required(),
                Textarea::make('short_description')
                    ->label(__('messages.service.short_description') . ':')
                    ->placeholder(__('messages.service.short_description'))
                    ->rows(5)
                    ->required(),
                SpatieMediaLibraryFileUpload::make('front_patient_profile')
                    ->label(__('messages.front_patient_testimonial.profile') . ':')
                    ->image()
                    ->avatar()
                    ->collection('front_patient_profile')
                    ->required(),
            ]);
    }
}
