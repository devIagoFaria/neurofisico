<?php

namespace App\Filament\Doctor\Resources\SmartPatientCards\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Filament\Forms\Components\ViewField;

class SmartPatientCardsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->live()
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        Hidden::make('address')->default(' '),
                        TextInput::make('template_name')
                            ->label(__('messages.smart_patient_card.templat_name') . ':')
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__('messages.smart_patient_card.enter_template_name')),

                        ColorPicker::make('header_color')
                            ->label(__('messages.smart_patient_card.header_color') . ':')
                            ->required(), // Default blue color

                        Toggle::make('show_email')
                            ->label(__('messages.smart_patient_card.email_show'))
                            ->default(true),

                        Toggle::make('show_phone')
                            ->label(__('messages.smart_patient_card.phone_show'))
                            ->default(true),

                        Toggle::make('show_dob')
                            ->label(__('messages.smart_patient_card.dob_show'))
                            ->default(true),

                        Toggle::make('show_blood_group')
                            ->label(__('messages.smart_patient_card.blood_group_show'))
                            ->default(true),

                        Toggle::make('show_address')
                            ->label(__('messages.smart_patient_card.address_show'))
                            ->default(true),

                        Toggle::make('show_patient_unique_id')
                            ->label(__('messages.smart_patient_card.unique_id_show'))
                            ->default(true),
                    ]),

                ViewField::make('card_preview')
                    ->previewView('filament.forms.components.smart-patient-card-preview')
                    ->hiddenLabel()
                    ->dehydrated(false),
            ]);
    }
}
