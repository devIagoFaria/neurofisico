<?php

namespace App\Filament\Doctor\Resources\Holidays\Tables;

use App\Models\Doctor;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class HolidaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $doctorId = Doctor::where('user_id', Auth::id())->value('id');
                $query->where('doctor_id', $doctorId);
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->label(__('messages.web.reason'))
                    ->wrap()
                    ->default(__('messages.common.n/a'))
                    ->searchable(),
                TextColumn::make('date')
                    ->sortable()
                    ->label(__('messages.appointment.date'))
                    ->date('d M Y')
                    ->badge()
                    ->searchable(),
            ])
            ->filters([
                DateRangeFilter::make('date')
                    ->label(__('messages.appointment.date'))
                    ->placeholder(__('messages.common.pick_date_range'))
                    ->startDate(Carbon::now()->startOfWeek()->subDay())
                    ->endDate(Carbon::now()->endOfWeek()->subDay()),
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_doctor_holiday'))
            ->recordActions([
                DeleteAction::make()
                    ->iconButton()
                    ->successNotificationTitle(__('messages.holiday.holiday') . ' ' . __('messages.medicine.deleted_successfully')),
            ])
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
