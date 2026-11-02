<?php

namespace App\Filament\Resources\Faqs\Pages;

use App\Filament\Resources\Faqs\FaqsResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditFaqs extends EditRecord
{
    protected static string $resource = FaqsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('messages.flash.faq_update');
    }
}
