<?php

namespace App\Filament\Resources\FrontPatientTestimonials\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class FrontPatientTestimonialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                SpatieMediaLibraryImageColumn::make('front_patient_profile')
                    ->label(__('messages.common.name'))
                    ->circular()
                    ->collection('front_patient_profile')
                    ->width(50)->imageHeight(50),
                TextColumn::make('name')
                    ->label('')
                    ->html()
                    ->color('primary')
                    ->weight(FontWeight::SemiBold)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('short_description')
                    ->label(__('messages.front_service.short_description'))
                    ->searchable()
                    ->sortable(),
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_testimonials'))
            ->filters([
                //
            ])
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->recordActions([
                EditAction::make()
                    ->iconButton(),
                DeleteAction::make()
                    ->iconButton()
                    ->successNotificationTitle(__('messages.flash.testimonial_delete')),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
