<?php

namespace App\Livewire;

use App\Models\VisitNote;
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

class VisitNoteTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;

    public $record;
    public $note_name;

    public function mount($record)
    {
        $this->record = $record;
    }

    protected function GetRecord()
    {
        return VisitNote::query()
            ->where('visit_id', $this->record->id);
    }

    public function addNoteAction(): Action
    {
        return Action::make('addNote')
            ->label(__('messages.common.add'))
            ->icon('heroicon-m-plus')
            ->modalHeading(__('messages.common.add') . ' ' . __('messages.visit.notes'))
            ->modalSubmitActionLabel(__('messages.common.save'))
            ->modalWidth('lg')
            ->form([
                Textarea::make('note_name')
                    ->label(__('messages.visit.notes') . ':')
                    ->placeholder(__('messages.visit.enter_note'))
                    ->required()
                    ->rows(4)
                    ->validationAttribute(__('messages.visit.notes')),
            ])
            ->action(function (array $data) {

                $this->record->notes()->create([
                    'note_name' => $data['note_name'],
                    'visit_id' => $this->record->id,
                ]);

                Notification::make()
                    ->title(__('messages.flash.note_added'))
                    ->success()
                    ->send();
            })
            ->hidden(fn() => Auth::user()->hasRole('patient'));
    }

    public function viewNoteAction(array $arguments = []): Action
    {
        $action = Action::make('viewNote')
            ->icon('heroicon-o-eye')
            ->iconButton()
            ->color('gray')
            ->modalWidth('lg')
            ->modalHeading(__('messages.common.view'))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('messages.common.cancel'))
            ->mountUsing(function (Schema $form, array $arguments) {
                $note = VisitNote::find($arguments['note'] ?? null);
                $form->fill([
                    'note_name' => $note?->note_name,
                ]);
            })
            ->form([
                Textarea::make('note_name')
                    ->label(__('messages.visit.notes'))
                    ->disabled()
                    ->rows(6),
            ]);

        return $action($arguments);
    }

    public function editNoteAction(array $arguments = []): Action
    {
        $action = Action::make('editNote')
            ->icon('heroicon-o-pencil-square')
            ->iconButton()
            ->modalHeading(__('messages.common.edit'))
            ->modalWidth('lg')
            ->modalSubmitActionLabel(__('messages.common.save'))
            ->mountUsing(function (Schema $form, array $arguments) {
                $note = VisitNote::find($arguments['note'] ?? null);
                $form->fill([
                    'note_name' => $note?->note_name,
                ]);
            })
            ->form([
                Textarea::make('note_name')
                    ->label(__('messages.visit.notes'))
                    ->placeholder(__('messages.visit.enter_note'))
                    ->required()
                    ->rows(4)
                    ->validationAttribute(__('messages.visit.notes')),
            ])
            ->action(function (array $data, array $arguments) {
                $note = VisitNote::find($arguments['note'] ?? null);

                if ($note) {
                    $note->update([
                        'note_name' => $data['note_name'],
                    ]);

                    Notification::make()
                        ->title(__('messages.flash.note_update'))
                        ->success()
                        ->send();

                    $this->record->refresh();
                }
            });

        return $action($arguments);
    }

    public function deleteNote($id): void
    {
        VisitNote::where('id', $id)
            ->where('visit_id', $this->record->id)
            ->delete();

        Notification::make()
            ->title(__('messages.flash.note_delete'))
            ->success()
            ->send();

        $this->record->refresh();
    }


    public function getNotesProperty()
    {
        return $this->record?->notes->pluck('note_name', 'id')->toArray() ?: [];
    }

    // public function table(Table $table): Table
    // {
    //     return $table
    //         ->query($this->GetRecord())
    //         ->defaultSort('id', 'desc')
    //         ->columns([
    //             TextColumn::make('note_name')
    //                 ->label(__('messages.common.name')),
    //         ])
    //         ->recordActions([
    //             DeleteAction::make()
    //                 ->iconButton()
    //                 ->successNotificationTitle(__('messages.flash.note_delete')),
    //         ])
    //         ->headerActions([
    //             CreateAction::make()
    //                 ->label(__('messages.common.add'))
    //                 ->modalWidth(Width::Medium)
    //                 ->successNotificationTitle(__('messages.flash.note_added'))
    //                 ->form(VisitNote::schema())
    //                 ->createAnother(false)
    //                 ->using(function (array $data) {
    //                     $data['visit_id'] = $this->record->id;
    //                     return VisitNote::create($data);
    //                 })
    //         ])
    //         ->recordActionsColumnLabel(__('messages.common.action'))
    //         ->filters([
    //             //
    //         ])
    //         ->emptyStateHeading(__('messages.common.no_data_available'));
    // }

    public function render()
    {
        return view('livewire.visit-note-table');
    }
}
