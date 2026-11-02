<?php

namespace App\Filament\Resources\DoctorSchedules\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\Doctors\DoctorResource;
use App\Filament\Tables\Columns\CustomIdentityColumn;

class DoctorSchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                CustomIdentityColumn::make('doctor.user.first_name')
                    ->avatar(fn ($record) => $record->doctor->user->profile_image)
                    ->html()
                    ->primary(fn ($record) => $record->doctor->user->full_name.' '.rating($record->doctor?->reviews?->avg('rating') ?? 0))
                    ->primaryUrl(fn ($record) => DoctorResource::getUrl('view', ['record' => $record->doctor->user->id]))
                    ->label(__('messages.doctor.doctor'))
                    ->color('#2b7fff')
                    ->toggleable(false)
                    ->secondary('doctor.user.email')
                    ->sortable(['first_name'])
                    ->searchable(['first_name','last_name','email'])
                    ->size('md'),
                TextColumn::make('created_at')
                    ->label(__('messages.doctor_session.session_time_in_minutes'))
                    ->formatStateUsing(function ($record) {
                        if ($record->session_meeting_time >= 60) {
                            return intdiv($record->session_meeting_time, 60).':'.($record->session_meeting_time % 60).' '.__('messages.common.hour');
                        } else {
                            return $record->session_meeting_time.' '.__('messages.common.minutes');
                        }
                    }
                    )
                    ->alignCenter()
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_doctor_schedule'))
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->recordActions([
                EditAction::make()
                    ->iconButton(),
                DeleteAction::make()
                    ->iconButton()
                    ->modalHeading(__('messages.common.delete') . ' ' . __('messages.doctor_sessions'))
                    ->successNotificationTitle(__('messages.flash.schedule_delete'))
            ])
            ->toolbarActions([
                //
            ]);
    }
}
