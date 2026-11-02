<?php

namespace App\Livewire;

use App\Models\Doctor;
use Filament\Tables\Table;
use App\Models\Prescription;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Widgets\TableWidget;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Support\Facades\Route;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use ToneGabes\Filament\Icons\Enums\Phosphor;
use App\Filament\Resources\Appointments\AppointmentResource;
use Illuminate\Support\Facades\Auth;

class AppointmentPrescriptionRelationTable extends TableWidget
{
    public $record;
    public $id;
    public function mount()
    {
        $this->id = Route::current()->parameter('record');
    }
    public function table(Table $table): Table
    {
        return $table
            ->query($this->getQuery())
            ->defaultSort('created_at', 'desc')
            ->heading('')
            ->columns([
                TextColumn::make('patient.user.full_name')
                    ->label(__('messages.appointment.patient'))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name']), 
                    // ->hidden(fn() => isRole('patient')),
                TextColumn::make('doctor.user.full_name')
                    ->label(__('messages.doctor.doctor'))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name'])
                    ->hidden(fn() => isRole('doctor')),
                TextColumn::make('medical_history')
                    ->label(__('messages.prescription.medical_history'))
                    ->default(__('messages.common.n/a'))
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('status')
                    ->label(__('messages.web.status'))
                    ->afterStateUpdated(function ($record, $state) {
                        notify(__('messages.flash.status_update'));
                    }),
            ])
            ->deferFilters(false)
            ->recordUrl(null)
            ->filters([
                SelectFilter::make('status')
                    ->options(Prescription::STATUS_ARR)
                    ->native(false)
                    ->label(__('messages.web.status'))
                    ->options([
                        Prescription::STATUS_ALL => __('messages.doctor_session.all'),
                        Prescription::ACTIVE => __('messages.common.active'),
                        Prescription::INACTIVE => __('messages.common.de_active'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (
                            ! isset($data['value']) ||
                            $data['value'] == Prescription::STATUS_ALL
                        ) {
                            return;
                        }
                        $query->where('status', $data['value']);
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                        ->url(fn () => AppointmentResource::getUrl(
                    'prescription-create',
                    ['record' => $this->record->id]
                ))->label(__('messages.prescription.new_prescription')),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->url(fn ($record) => AppointmentResource::getUrl(
                        'prescription-medicine-show',
                        ['record' => $record->id]
                    )),
                EditAction::make()
                    ->iconButton()
                    ->url(fn ($record) => AppointmentResource::getUrl(
                        'prescription-edit',
                        ['record' => $record->id]
                    )),
                Action::make('pdf')
                    ->label(__('messages.common.download'))
                    ->icon(Phosphor::Download)
                    ->color('success')
                    ->iconButton()
                    ->url(fn($record) => route('prescriptions.pdf', $record->id))
                    ->openUrlInNewTab(),
                DeleteAction::make()
                    ->iconButton(),
            ])
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->emptyStateHeading(__('messages.custom_table_message.no_prescriptions'))
            ->emptyStateDescription('')
            ->toolbarActions([
                // BulkActionGroup::make([
                //     //
                // ]),
            ]);
    }

    protected function getQuery(): Builder
    {
        $query = Prescription::query()
            ->with(['patient.user', 'doctor.user']);

        if (auth()->user()->hasRole('doctor')) {
            $doctorId = Doctor::where('user_id', auth()->id())->value('id');
            $query->where('doctor_id', $doctorId);
        }

        if ($this->record->id) {
            $query->where('appointment_id', $this->record->id);
        }

        return $query;
    }
}
