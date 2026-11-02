<?php

namespace App\Filament\Resources\MedicineBrands\Schemas;

use App\Models\Brand;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class MedicineBrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
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
                    ])->columns(2)->columnSpanFull(),
            ]);
    }
}
