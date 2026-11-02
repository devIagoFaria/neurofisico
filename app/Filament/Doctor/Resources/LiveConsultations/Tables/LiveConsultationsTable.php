<?php

namespace App\Filament\Doctor\Resources\LiveConsultations\Tables;

use App\Models\Doctor;
use App\Models\LiveConsultation;
use App\Models\Patient;
use App\Repositories\ZoomRepository;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class LiveConsultationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (Auth::user()->hasRole('doctor')) {
                    $doctorId = Doctor::where('user_id', Auth::id())->value('id');
                    $query->where('doctor_id', $doctorId);
                }

                if (Auth::user()->hasRole('patient')) {
                    $patientId = Patient::where('user_id', Auth::id())->value('id');
                    $query->where('patient_id', $patientId);
                }
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('consultation_title')
                    ->label(__('messages.live_consultation.consultation_title'))
                    ->sortable()
                    ->action(
                        ViewAction::make()
                            ->modalWidth('2xl')
                    )
                    ->color('primary')
                    ->searchable(),
                TextColumn::make('consultation_date')
                    ->label(__('messages.appointment.date'))
                    ->badge()
                    ->formatStateUsing(fn($record) => $record->consultation_date->format('d M Y g:i A'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.full_name')
                    ->label(__('messages.live_consultation.created_by'))
                    ->sortable()
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('doctor.user.full_name')
                    ->label(__('messages.live_consultation.created_for')),
                TextColumn::make('patient.user.full_name')
                    ->label(__('messages.appointment.patient')),
                TextColumn::make('status_text')
                    ->label(__('messages.doctor.status'))
                    ->badge()
                    ->state(fn(LiveConsultation $record) => $record->status)
                    ->formatStateUsing(fn(int $state) => match ($state) {
                        0 => __('messages.live_consultation.awaited'),
                        1 => __('messages.common.cancelled'),
                        2 => __('messages.live_consultation.finished'),
                        default => __('Unknown'),
                    })
                    ->color(fn(int $state) => match ($state) {
                        0 => 'warning',
                        1 => 'danger',
                        2 => 'success',
                        default => 'gray',
                    })
                    ->visible(fn() => isRole('patient')),
                ViewColumn::make('status')
                    ->label(__('messages.doctor.status'))
                    ->view('filament.resources.live-consultations.columns.status')
                    ->hidden(fn() => isRole('patient')),
                TextColumn::make('password')
                    ->label(__('messages.patient.password'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('messages.doctor.status'))
                    ->native(false)
                    ->options([
                        LiveConsultation::ALL => __('messages.doctor_session.all'),
                        LiveConsultation::STATUS_AWAITED => __('messages.live_consultation.awaited'),
                        LiveConsultation::STATUS_CANCELLED => __('messages.common.cancelled'),
                        LiveConsultation::STATUS_FINISHED => __('messages.live_consultation.finished'),
                    ])
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_consultations'))
            ->recordAction(null)
            ->deferFilters(false)
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->recordActions([
                Action::make('start_consultation_zoom')
                    ->icon(Phosphor::VideoCamera)
                    ->iconButton()
                    ->visible(
                        fn(LiveConsultation $record) =>
                        $record->status === LiveConsultation::STATUS_AWAITED &&
                            (
                                Auth::user()->hasRole('doctor') ||
                                Auth::user()->hasRole('patient')
                            )
                    )
                    ->modalHeading(function ($record) {
                        try {
                            /** @var ZoomRepository $zoomRepo */
                            $zoomRepo = App::make(ZoomRepository::class, ['createdBy' => $record->created_by]);
                            $zoomLiveData = $zoomRepo->zoomGet($record->meeting_id);

                            return $record->consultation_title;
                        } catch (\Exception $e) {
                            return false;
                        }
                    })
                    ->modalWidth('2xl')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->schema(function ($record) {
                        static $notified = false;
                        try {
                            /** @var ZoomRepository $zoomRepo */
                            $zoomRepo = App::make(ZoomRepository::class, ['createdBy' => $record->created_by]);
                            $zoomLiveData = $zoomRepo->zoomGet($record->meeting_id);

                            return [
                                Group::make([
                                    TextEntry::make('user.full_name')
                                        ->label(__('messages.live_consultation.host_video') . ':'),
                                    TextEntry::make('consultation_date')
                                        ->label(__('messages.live_consultation.consultation_date') . ':')
                                        ->formatStateUsing(fn($record) => $record->consultation_date->format('g:i A, jS F Y')),
                                    TextEntry::make('consultation_duration_minutes')
                                        ->label(__('messages.live_consultation.duration') . ':'),
                                    Group::make([
                                        TextEntry::make('meeting_id')
                                            ->label(__('messages.common.status') . ':')
                                            ->formatStateUsing(function ($state) use ($zoomLiveData) {
                                                if ($zoomLiveData->status == 'started') {
                                                    return 'Started';
                                                } else {
                                                    return 'Awaited';
                                                }
                                            }),
                                        Action::make('join_now')
                                            ->icon('heroicon-s-video-camera')
                                            ->label(function () {
                                                return getLogInUser()->hasRole('Patient') ? __('messages.live_consultation.join_now') : __('messages.live_consultation.start_now');
                                            })
                                            ->url(function ($record) use ($zoomLiveData) {
                                                return getLogInUser()->hasRole('Patient') ? $record->meta['join_url'] : ($zoomLiveData->status === 'started' ? 'javascript:void(0);' : $record->meta['start_url']);
                                            }, true)
                                            ->disabled(function () use ($zoomLiveData) {
                                                return $zoomLiveData->status === 'started';
                                            })->extraAttributes(['class' => 'ms-auto']),
                                    ])->columns(2)
                                        ->extraAttributes(['class' => 'border-t border-gray-200 pt-3'])
                                        ->columnSpanFull(),
                                ])->columns(2),
                            ];
                        } catch (\Exception $e) {
                            if (! $notified) {
                                $notified = true;

                                Notification::make()
                                    ->danger()
                                    ->title($e->getMessage())
                                    ->send();
                            }
                            return [];
                        }
                    }),
                DeleteAction::make()
                    ->iconButton()
                    ->successNotificationTitle(__('messages.flash.live_consultation_delete'))
                    ->visible(fn() => Auth::user()->hasRole('doctor')),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
