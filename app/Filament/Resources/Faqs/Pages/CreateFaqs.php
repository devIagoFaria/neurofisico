<?php

namespace App\Filament\Resources\Faqs\Pages;

use App\Filament\Resources\Faqs\FaqsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFaqs extends CreateRecord
{
    protected static string $resource = FaqsResource::class;
    protected static bool $canCreateAnother = false;
    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return __('messages.flash.faq_creat');
    }
}
