<?php

namespace App\Filament\Resources\PurchaseMedicines\Pages;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use App\Livewire\PurchaseMedicineDetail;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use App\Filament\Resources\PurchaseMedicines\PurchaseMedicinesResource;
use App\Models\PurchaseMedicine;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Livewire;
use Illuminate\Support\HtmlString;

class ViewPurchaseMedicine extends ViewRecord
{
    protected static string $resource = PurchaseMedicinesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }

    public function infolist(Schema $infolist): Schema
    {
        return $infolist->components([
            Section::make('')
                ->schema([
                    Group::make([
                        TextEntry::make('purchase_no')
                            ->badge()
                            ->prefix('#')
                            ->label(__('messages.purchase_medicine.purchase_number') . ':'),

                        TextEntry::make('total')
                            ->label(__('messages.purchase_medicine.total') . ':'),

                        TextEntry::make('tax')
                            ->label(__('messages.purchase_medicine.tax_amount') . ':')
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', '')),

                        TextEntry::make('discount')
                            ->label(__('messages.purchase_medicine.discount') . ':')
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', '')),

                        TextEntry::make('net_amount')
                            ->label(__('messages.purchase_medicine.net_amount') . ':'),

                        TextEntry::make('note')
                            ->label(__('messages.purchase_medicine.note') . ':')
                            ->formatStateUsing(fn($state) => $state ?: __('messages.common.n/a'))
                            ->default(__('messages.common.n/a')),

                        Livewire::make(PurchaseMedicineDetail::class)
                            ->columnSpanFull(),

                        Grid::make([
                            'default' => 8,
                            'sm' => 8,
                            'md' => 10,
                            'lg' => 10,
                            'xl' => 12,
                        ])
                            ->schema([
                                Grid::make()
                                    ->schema([])
                                    ->columnSpan([
                                        'default' => 4,
                                        'sm' => 5,
                                        'md' => 7,
                                        'lg' => 7,
                                        'xl' => 9,
                                    ]),

                                Grid::make(1)
                                    ->schema([
                                        TextEntry::make('total')
                                            ->label(__('messages.purchase_medicine.total'))
                                            ->inlineLabel()
                                            ->extraEntryWrapperAttributes(['class' => 'total-label'])
                                            ->extraAttributes(['style' => 'text-align: right;'])
                                            ->columnSpanFull(),

                                        TextEntry::make('tax')
                                            ->label(__('messages.purchase_medicine.tax') . ':')
                                            ->extraEntryWrapperAttributes(['class' => 'total-label'])
                                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                                            ->inlineLabel()
                                            ->extraAttributes(['style' => 'text-align: right;']),

                                        TextEntry::make('discount')
                                            ->label(__('messages.purchase_medicine.discount') . ':')
                                            ->extraEntryWrapperAttributes(['class' => 'total-label'])
                                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                                            ->inlineLabel()
                                            ->extraAttributes(['style' => 'text-align: right;']),

                                        TextEntry::make('net_amount')
                                            ->label(__('messages.purchase_medicine.net_amount') . ':')
                                            ->extraEntryWrapperAttributes(['class' => 'total-label'])
                                            ->wrap(false)
                                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                                            ->inlineLabel()
                                            ->extraAttributes(['style' => 'text-align: right;']),
                                    ])
                                    // ->columnSpan(3),
                                    ->columnSpan([
                                        'default' => 4,
                                        'sm' => 3,
                                        'md' => 3,
                                        'lg' => 3,
                                    ]),
                            ])->columnSpanFull(),

                    ])->columnSpan([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 2,
                        'lg' => 2,
                        'xl' => 5,
                        '2xl' => 6,
                    ])->columns([
                        'default' => 2,
                        'sm' => 2,
                        'md' => 3,
                    ]),

                    Section::make(__('messages.purchase_medicine.other_details'))
                        ->schema([
                            TextEntry::make('payment_note')
                                ->label(__('messages.purchase_medicine.payment_note'))
                                ->formatStateUsing(fn($state) => $state ?: __('messages.common.n/a'))
                                ->default(__('messages.common.n/a')),
                            TextEntry::make('note')
                                ->label(__('messages.visit.notes'))
                                ->formatStateUsing(fn($state) => $state ?: __('messages.common.n/a'))
                                ->default(__('messages.common.n/a')),
                            TextEntry::make('payment_type')
                                ->label(__('messages.purchase_medicine.payment_mode'))
                                ->formatStateUsing(
                                    fn($state) =>
                                    PurchaseMedicine::PAYMENT_METHOD[$state]
                                        ?? __('messages.common.n/a')
                                ),
                            TextEntry::make('created_at')
                                ->label(__('messages.web.created_at'))
                                ->since(),
                        ])
                        ->columns([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 2,
                            'lg' => 2,
                            'xl' => 1,
                        ])->columnSpan([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 2,
                            'lg' => 2,
                            'xl' => 2,
                        ]),

                ])
                // ->columns(8)
                ->columns([
                    'default' => 1,
                    'sm' => 2,
                    'md' => 2,
                    'lg' => 2,
                    'xl' => 7,
                    '2xl' => 8,
                ])
                ->columnSpanFull(),

        ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // PurchaseMedicineDetail::class,
        ];
    }
}
