<?php

namespace App\Livewire;

use App\Models\VisitProblem;
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

class VisitProblemTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;

    public $record;
    public $problem_name;

    public function mount($record)
    {
        $this->record = $record;
    }

    protected function GetRecord()
    {
        return VisitProblem::query()
            ->where('visit_id', $this->record->id);
    }

    public function addProblemAction(): Action
    {
        return Action::make('addProblem')
            ->label(__('messages.common.add'))
            ->icon('heroicon-m-plus')
            ->modalHeading(__('messages.common.add') . ' ' . __('messages.prescription.problem'))
            ->modalSubmitActionLabel(__('messages.common.save'))
            ->modalWidth('lg')
            ->form([
                Textarea::make('problem_name')
                    ->label(__('messages.prescription.problem'))
                    ->placeholder(__('messages.visit.enter_problem'))
                    ->required()
                    ->rows(4)
                    ->validationAttribute(__('messages.prescription.problem')),
            ])
            ->action(function (array $data) {

                $this->record->problems()->create([
                    'problem_name' => $data['problem_name'],
                    'visit_id' => $this->record->id,
                ]);

                Notification::make()
                    ->title(__('messages.flash.problem_added'))
                    ->success()
                    ->send();
            })
            ->hidden(fn() => Auth::user()->hasRole('patient'));
    }

    public function deleteProblem($id): void
    {
        VisitProblem::where('id', $id)
            ->where('visit_id', $this->record->id)
            ->delete();

        Notification::make()
            ->title(__('messages.flash.problem_delete'))
            ->success()
            ->send();

        $this->record->refresh();
    }

    public function viewProblemAction(array $arguments = []): Action
    {
        $action = Action::make('viewProblem')
            ->icon('heroicon-o-eye')
            ->iconButton()
            ->color('gray')
            ->modalHeading(__('messages.common.view'))
            ->modalWidth('lg')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('messages.common.cancel'))
            ->mountUsing(function (Schema $form, array $arguments) {
                $problem = VisitProblem::find($arguments['problem'] ?? null);
                $form->fill([
                    'problem_name' => $problem?->problem_name,
                ]);
            })
            ->form([
                Textarea::make('problem_name')
                    ->label(__('messages.prescription.problem'))
                    ->disabled()
                    ->rows(6),
            ]);

        return $action($arguments);
    }

    public function editProblemAction(array $arguments = []): Action
    {
        $action = Action::make('editProblem')
            ->icon('heroicon-o-pencil-square')
            ->iconButton()
            ->modalWidth('lg')
            ->modalHeading(__('messages.common.edit'))
            ->modalSubmitActionLabel(__('messages.common.save'))
            ->mountUsing(function (Schema $form, array $arguments) {
                $problem = VisitProblem::find($arguments['problem'] ?? null);
                $form->fill([
                    'problem_name' => $problem?->problem_name,
                ]);
            })
            ->form([
                Textarea::make('problem_name')
                    ->label(__('messages.prescription.problem') . ':')
                    ->placeholder(__('messages.visit.enter_problem'))
                    ->required()
                    ->validationAttribute(__('messages.prescription.problem'))
                    ->rows(5),
            ])
            ->action(function (array $data, array $arguments) {
                $problem = VisitProblem::find($arguments['problem'] ?? null);

                if ($problem) {
                    $problem->update([
                        'problem_name' => $data['problem_name'],
                    ]);

                    Notification::make()
                        ->title(__('messages.flash.problem_update'))
                        ->success()
                        ->send();

                    $this->record->refresh();
                }
            });

        return $action($arguments);
    }

    public function getProblemsProperty()
    {
        return $this->record?->problems->pluck('problem_name', 'id')->toArray() ?: [];
    }

    public function render()
    {
        return view('livewire.visit-problem-table');
    }
}
