<?php

namespace App\Livewire;

use App\Models\VisitObservation;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Filament\Infolists\Components\TextEntry;

class VisitObservationTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;

    public $record;
    public $observation_name;

    public function mount($record)
    {
        $this->record = $record;
    }

    protected function GetRecord()
    {
        return VisitObservation::query()
            ->where('visit_id', $this->record->id);
    }

    public function addObservationAction(): Action
    {
        return Action::make('addObservation')
            ->label(__('messages.common.add'))
            ->icon('heroicon-m-plus')
            ->modalHeading(__('messages.common.add') . ' ' . __('messages.visit.observations'))
            ->modalSubmitActionLabel(__('messages.common.save'))
            ->modalWidth('lg')
            ->form([
                Textarea::make('observation_name')
                    ->label(__('messages.visit.observations') . ':')
                    ->placeholder(__('messages.visit.enter_observation'))
                    ->required()
                    ->rows(4)
                    ->validationAttribute(__('messages.visit.observations')),
            ])
            ->action(function (array $data) {

                $this->record->observations()->create([
                    'observation_name' => $data['observation_name'],
                    'visit_id' => $this->record->id,
                ]);

                Notification::make()
                    ->title(__('messages.flash.observation_added'))
                    ->success()
                    ->send();
            })
            ->hidden(fn() => Auth::user()->hasRole('patient'));
    }

    public function viewObservationAction(array $arguments = []): Action
    {
        $action = Action::make('viewObservation')
            ->icon('heroicon-o-eye')
            ->iconButton()
            ->color('gray')
            ->modalWidth('lg')
            ->modalHeading(__('messages.common.view'))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('messages.common.cancel'))
            ->mountUsing(function (Schema $form, array $arguments) {
                $observation = VisitObservation::find($arguments['observation'] ?? null);
                $form->fill([
                    'observation_name' => $observation?->observation_name,
                ]);
            })
            ->form([
                Textarea::make('observation_name')
                    ->label(__('messages.visit.observations'))
                    ->disabled()
                    ->rows(6),
            ]);

        return $action($arguments);
    }

    public function editObservationAction(array $arguments = []): Action
    {
        $action = Action::make('editObservation')
            ->icon('heroicon-o-pencil-square')
            ->iconButton()
            ->modalWidth('lg')
            ->modalHeading(__('messages.common.edit'))
            ->modalSubmitActionLabel(__('messages.common.save'))
            ->mountUsing(function (Schema $form, array $arguments) {
                $observation = VisitObservation::find($arguments['observation'] ?? null);
                $form->fill([
                    'observation_name' => $observation?->observation_name,
                ]);
            })
            ->form([
                Textarea::make('observation_name')
                    ->label(__('messages.visit.observations') . ':')
                    ->placeholder(__('messages.visit.enter_observation'))
                    ->required()
                    ->validationAttribute(__('messages.visit.observations'))
                    ->rows(5),
            ])
            ->action(function (array $data, array $arguments) {
                $observation = VisitObservation::find($arguments['observation'] ?? null);

                if ($observation) {
                    $observation->update([
                        'observation_name' => $data['observation_name'],
                    ]);

                    Notification::make()
                        ->title(__('messages.flash.observation_update'))
                        ->success()
                        ->send();

                    $this->record->refresh();
                }
            });

        return $action($arguments);
    }

    public function deleteObservation($id): void
    {
        VisitObservation::where('id', $id)
            ->where('visit_id', $this->record->id)
            ->delete();

        Notification::make()
            ->title(__('messages.flash.observation_delete'))
            ->success()
            ->send();

        $this->record->refresh();
    }


    public function getObservationsProperty()
    {
        return $this->record?->observations->pluck('observation_name', 'id')->toArray() ?: [];
    }

    public function render()
    {
        return view('livewire.visit-observation-table');
    }
}
