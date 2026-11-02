<?php

namespace App\Filament\Resources\Banners\Schemas;

use App\Models\Slider;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->label(__('messages.slider.title').':')
                            ->placeholder(__('messages.slider.title'))
                            ->required(),
                        Textarea::make('short_description')
                            ->label(__('messages.slider.short_description').':')
                            ->placeholder(__('messages.slider.short_description'))
                            ->rows(5)
                            ->required(),
                        SpatieMediaLibraryFileUpload::make('slider_image')
                            ->label(__('messages.slider.image').':')
                            ->image()
                            ->avatar()
                            ->collection(Slider::SLIDER_IMAGE)
                            ->disk(config('app.media_disk'))
                            ->required(),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }
}
