<?php

namespace App\Filament\Resources\Categories\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\Categories\CategoriesResource;

class ManageCategories extends ManageRecords
{
    protected static string $resource = CategoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth(Width::Medium)
                ->createAnother(false)
                ->successNotificationTitle(__('messages.flash.medicine_category_create')),
        ];
    }
}
