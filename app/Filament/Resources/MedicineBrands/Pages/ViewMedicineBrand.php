<?php

namespace App\Filament\Resources\MedicineBrands\Pages;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Tabs;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Livewire;
use App\Livewire\MedicineBrandDetailsTable;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\MedicineBrands\MedicineBrandResource;

class ViewMedicineBrand extends ViewRecord
{
    protected static string $resource = MedicineBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            backActionResource(static::$resource::getUrl()),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.medicine.medicine_brands_details'))
                    ->columns(2)
                    ->columnSpanFull()
                    ->components([
                        TextEntry::make('name')
                            ->label(__('messages.medicine.brand')),
                        TextEntry::make('email')
                            ->label(__('messages.web.email'))
                            ->default(__('messages.common.n/a')),
                        TextEntry::make('phone')
                            ->label(__('messages.web.phone'))
                            ->default(__('messages.common.n/a')),
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
                                Livewire::make(MedicineBrandDetailsTable::class)
                            ]),
                    ]),
            ]);
    }
}
