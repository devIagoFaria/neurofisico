<?php

namespace App\Filament\Resources\Medicines\Schemas;

use App\Models\Medicine;
use App\Models\Category;
use App\Models\Brand;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class MedicinesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label(__('messages.medicine.medicine').':')
                            ->required()
                            ->unique(Medicine::class, 'name', ignoreRecord: true)
                            ->placeholder(__('messages.medicine.medicine')),
                        Select::make('category_id')
                            ->label(__('messages.medicine.category').':')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->relationship('category', 'name')
                            ->placeholder(__('messages.medicine.category'))
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(__('messages.medicine.category').':')
                                    ->placeholder(__('messages.medicine.category'))
                                    ->required()
                                    ->unique(Category::class, 'name'),
                                Toggle::make('is_active')
                                    ->label(__('messages.common.active').':')
                                    ->inline(false)
                                    ->required()
                                    ->default(true)
                                    ->columnSpanFull(),
                            ])
                            ->createOptionAction(function (Action $action) {
                                return $action
                                    ->modalWidth('md')
                                    ->modalHeading(__('messages.common.add') . ' ' . __('messages.medicine.category'))
                                    ->modalSubmitActionLabel(__('messages.common.save'));
                            })
                            ->createOptionUsing(function (array $data) {
                                $category = Category::create($data);
                                return $category->id;
                            }),
                        Select::make('brand_id')
                            ->label(__('messages.medicine.brand').':')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->relationship('brand', 'name')
                            ->placeholder(__('messages.medicine.brand'))
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(__('messages.medicine.brand').':')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Brand::class, 'name', ignoreRecord: true)
                                    ->placeholder(__('messages.medicine.brand')),
                                PhoneInput::make('phone')
                                    ->label(__('messages.web.phone').':')
                                    ->placeholder(__('messages.web.phone')),
                                TextInput::make('email')
                                    ->label(__('messages.web.email').':')
                                    ->maxLength(255)
                                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? strtolower($state) : $state)
                                    ->unique(Brand::class, 'email', ignoreRecord: true)
                                    ->email()
                                    ->placeholder(__('messages.web.email')),
                                    ])
                            ->createOptionAction(function (Action $action) {
                                return $action
                                    ->label(__('messages.common.add_new'))
                                    ->modalWidth('md')
                                    ->modalHeading(__('messages.common.add') . ' ' .__('messages.medicine.brand'))
                                    ->modalSubmitActionLabel(__('messages.common.save'));
                            })
                            ->createOptionUsing(function (array $data) {
                                $brand = Brand::create($data);
                                return $brand->id;
                            }),
                        TextInput::make('salt_composition')
                            ->label(__('messages.medicine.salt_composition').':')
                            ->required()
                            ->placeholder(__('messages.medicine.salt_composition')),
                        TextInput::make('buying_price')
                            ->label(__('messages.medicine.buying_price').':')
                            ->required()
                            ->placeholder(__('messages.medicine.buying_price')),
                        TextInput::make('selling_price')
                            ->label(__('messages.medicine.selling_price').':')
                            ->required()
                            ->placeholder(__('messages.medicine.selling_price')),
                        Textarea::make('side_effects')
                            ->label(__('messages.medicine.side_effects').':')
                            ->placeholder(__('messages.medicine.side_effects'))
                            ->rows(4),
                        Textarea::make('description')
                            ->label(__('messages.medicine.description').':')
                            ->placeholder(__('messages.medicine.description'))
                            ->rows(4),
                        Hidden::make('quantity')
                            ->default(fn ($record) => $record?->quantity ?? 0),
                        Hidden::make('available_quantity')
                            ->default(fn ($record) => $record?->available_quantity ?? 0),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }
}
