<?php

namespace App\Livewire;

use App\Models\VisitPrescription;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;

class VisitPrescriptionTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;

    public $record;

    public function mount($record)
    {
        $this->record = $record;
    }

    protected function GetRecord()
    {
        return VisitPrescription::query()
            ->where('visit_id', $this->record->id);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->GetRecord())
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('prescription_name')
                    ->label(__('messages.prescription.name')),
                TextColumn::make('frequency')
                    ->label(__('messages.frequency')),
                TextColumn::make('duration')
                    ->label(__('messages.prescription.duration')),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalWidth(Width::Medium)
                    ->modalHeading(__('messages.common.view') . __('messages.visit.visit') . ' ' . __('messages.visit.prescriptions'))
                    ->iconButton()
                    ->infolist([
                        TextEntry::make('prescription_name')
                            ->label(__('messages.prescription.name')),
                        TextEntry::make('frequency')
                            ->label(__('messages.frequency')),
                        TextEntry::make('duration')
                            ->label(__('messages.prescription.duration')),
                        TextEntry::make('description')
                            ->label(__('messages.visit.description'))
                            ->placeholder(__('messages.common.n/a')),
                    ]),
                EditAction::make()
                    ->modalWidth(Width::Medium)
                    ->iconButton()
                    ->modalHeading(__('messages.common.edit') . __('messages.visit.visit') . ' ' . __('messages.visit.prescriptions'))
                    ->form(VisitPrescription::schema())
                    ->successNotificationTitle(__('messages.flash.visit_prescription_update'))
                    ->hidden(fn() => Auth::user()->hasRole('patient')),
                DeleteAction::make()
                    ->iconButton()
                    ->successNotificationTitle(__('messages.flash.prescription_delete'))
                    ->hidden(fn() => Auth::user()->hasRole('patient')),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('messages.common.add'))
                    ->modalWidth(Width::Medium)
                    ->modalHeading(__('messages.common.add') . __('messages.visit.visit') . ' ' . __('messages.visit.prescriptions'))
                    ->successNotificationTitle(__('messages.flash.visit_prescription_added'))
                    ->form(VisitPrescription::schema())
                    ->createAnother(false)
                    ->using(function (array $data) {
                        $data['visit_id'] = $this->record->id;
                        return VisitPrescription::create($data);
                    })
                    ->hidden(fn() => Auth::user()->hasRole('patient')),
            ])
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->filters([
                //
            ])
            ->emptyStateDescription('')
            ->emptyStateHeading(__('messages.custom_table_message.no_prescriptions'));
    }

    public function render()
    {
        return view('livewire.visit-prescription-table');
    }
}
