<?php

namespace App\Filament\Resources\Enquiries\Pages;

use App\Models\Enquiry;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Grid;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\Enquiries\EnquiriesResource;

class ViewEnquiry extends ViewRecord
{
    protected static string $resource = EnquiriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }

    public function mount($record): void
    {
        parent::mount($record);

        $enquiry = $this->record;

        if (! $enquiry->view) {
            $enquiry->update(['view' => true]);
        }
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('name')
                                ->label(__('messages.web.name') . ':'),
                            TextEntry::make('email')
                                ->label(__('messages.web.email') . ':'),
                            TextEntry::make('phone')
                                ->label(__('messages.web.phone') . ':'),
                            TextEntry::make('subject')
                                ->label(__('messages.web.subject') . ':'),
                            TextEntry::make('message')
                                ->label(__('messages.web.message') . ':'),
                            TextEntry::make('created_at')
                                ->label(__('messages.patient.registered_on') . ':')
                                ->since(),
                            TextEntry::make('updated_at')
                                ->label(__('messages.patient.last_updated') . ':')
                                ->since(),
                        ])
                ])
                ->columnSpanFull(),
            ]);
    }
}
