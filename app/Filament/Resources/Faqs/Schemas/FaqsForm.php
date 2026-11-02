<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class FaqsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                    TextInput::make('question')
                        ->label(__('messages.faq.question')  . ':')
                        ->placeholder(__('messages.faq.question'))
                        ->required(),
                    Textarea::make('answer')
                        ->label(__('messages.faq.answer')  . ':')
                        ->placeholder(__('messages.faq.answer'))
                        ->rows(5)
                        ->required(),
                ])->columnSpanFull(),
            ]);
    }
}
