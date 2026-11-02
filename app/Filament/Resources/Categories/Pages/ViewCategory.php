<?php

namespace App\Filament\Resources\Categories\Pages;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Schemas\Components\Tabs;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Livewire;
use Filament\Infolists\Components\TextEntry;
use App\Livewire\MedicineCategoryDetailsTable;
use App\Filament\Resources\Categories\CategoriesResource;

class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->modalWidth(Width::Medium)
                ->successNotificationTitle(__('messages.flash.medicine_category_update')),
            backActionResource(static::$resource::getUrl()),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.medicine.medicine_category_details'))
                    ->columns(3)
                    ->columnSpanFull()
                    ->components([
                        TextEntry::make('name')
                            ->label(__('messages.admin_dashboard.name')),
                        TextEntry::make('is_active')
                            ->label(__('messages.web.status'))
                            ->formatStateUsing(fn ($state) =>
                                $state ? __('messages.common.active') : __('messages.filter.deactive')
                            )
                            ->badge()
                            ->color(fn ($state) => $state ? 'success' : 'danger'),
                        TextEntry::make('created_at')
                            ->label(__('messages.web.created_at'))
                            ->since(),

                        TextEntry::make('updated_at')
                            ->label(__('messages.patient.last_updated'))
                            ->since(),
                    ]),
                Tabs::make(__('Details'))
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make(__('messages.medicine.medicines'))
                            ->schema([
                                Livewire::make(MedicineCategoryDetailsTable::class)
                            ]),
                    ]),
            ]);
    }

}
