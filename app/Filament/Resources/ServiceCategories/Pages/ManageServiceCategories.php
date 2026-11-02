<?php

namespace App\Filament\Resources\ServiceCategories\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\ServiceCategories\ServiceCategoriesResource;

class ManageServiceCategories extends ManageRecords
{
    protected static string $resource = ServiceCategoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth(Width::Medium)
                ->createAnother(false)
                ->successNotificationTitle(__('messages.flash.service_cat_create')),
        ];
    }
}
