<?php

namespace App\Filament\Resources\DoctorHolidays\Tables;

use Carbon\Carbon;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\Doctors\DoctorResource;
use App\Filament\Tables\Columns\CustomIdentityColumn;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class DoctorHolidaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                CustomIdentityColumn::make('doctor.user.first_name')
                    ->avatar(fn ($record) => $record->doctor->user->profile_image)
                    ->html()
                    ->primary(fn ($record) => $record->doctor->user->full_name.' '.rating($record->doctor?->reviews?->avg('rating') ?? 0))
                    ->label(__('messages.doctor.doctor'))
                    ->color('#2b7fff')
                    ->toggleable(false)
                    ->primaryUrl(fn ($record) => DoctorResource::getUrl('view',['record' => $record->doctor->user_id]))
                    ->secondary('doctor.user.email')
                    ->sortable(['first_name'])
                    ->searchable(['first_name','last_name','email'])
                    ->size('md'),
                TextColumn::make('name')
                    ->sortable()
                    ->label(__('messages.web.reason'))
                    ->wrap()
                    ->default(__('messages.common.n/a'))
                    ->searchable(),
                TextColumn::make('date')
                    ->sortable()
                    ->label(__('messages.holiday.holiday_date'))
                    ->date('d M Y')
                    ->badge()
                    ->searchable(),
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->emptyStateHeading(__('messages.custom_table_message.no_doctor_holiday'))
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->filters([
                DateRangeFilter::make('date')
                    ->label(__('messages.appointment.date'))
                    ->placeholder(__('messages.common.pick_date_range'))
                    ->startDate(Carbon::now()->startOfWeek()->subDay())
                    ->endDate(Carbon::now()->endOfWeek()->subDay()),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->iconButton()
                    ->successNotificationTitle(__('messages.holiday.holiday').' '.__('messages.medicine.deleted_successfully')),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
