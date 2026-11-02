<?php

namespace App\Filament\Resources\Banners\Tables;

use App\Models\Slider;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                SpatieMediaLibraryImageColumn::make('slider_image')
                    ->label(__('messages.slider.image'))
                    ->circular()
                    ->collection(Slider::SLIDER_IMAGE)
                    ->disk(config('app.media_disk')),
                TextColumn::make('page_type')
                    ->label(__('messages.web.page_type'))
                    ->formatStateUsing(fn (?string $state) => Slider::pageTypeOptions()[$state] ?? $state ?? '-')
                    ->badge(),
                TextColumn::make('title')
                    ->label(__('messages.front_service.title')),
                TextColumn::make('short_description')
                    ->label(__('messages.front_service.short_description')),
            ])
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->emptyStateHeading(__('messages.custom_table_message.no_banners'))
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
