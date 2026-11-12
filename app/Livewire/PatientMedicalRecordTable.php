<?php

namespace App\Livewire;

use App\Models\MedicalRecord;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use App\Models\Appointment;
use Illuminate\Validation\ValidationException;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class PatientMedicalRecordTable extends TableWidget
{
    public int $patientId;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MedicalRecord::query()
                    ->where('patient_id', $this->patientId)
            )
            ->columns([
                TextColumn::make('appointment.date')
                    ->label('Consulta')
                    ->date('d/m/Y')
                    ->placeholder('Sem consulta'),

                TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),

                TextColumn::make('content')
                    ->label('Anotação')
                    ->limit(80),

                TextColumn::make('author.full_name')
                    ->label('Responsável'),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nova anotação')
                    ->model(MedicalRecord::class)
                    ->schema([
                        Select::make('appointment_id')
                            ->label('Consulta')
                            ->options(fn () =>
                                Appointment::query()
                                    ->where('patient_id', $this->patientId)
                                    ->orderByDesc('date')
                                    ->get()
                                    ->mapWithKeys(fn ($appointment) => [
                                        $appointment->id =>
                                            "Consulta #{$appointment->id} - {$appointment->date}",
                                    ])
                            )
                            ->searchable(),

                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),

                        RichEditor::make('content')
                            ->label('Anotação clínica')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->mutateDataUsing(function (array $data): array {
                        if (
                            ! empty($data['appointment_id']) &&
                            ! Appointment::whereKey($data['appointment_id'])
                                ->where('patient_id', $this->patientId)
                                ->exists()
                        ) {
                            throw ValidationException::withMessages([
                                'appointment_id' => 'A consulta não pertence a este paciente.',
                            ]);
                        }

                        $data['patient_id'] = $this->patientId;
                        $data['user_id'] = auth()->id();

                        return $data;
                    }),
            ])
            ->recordActions([

                // VISUALIZAR ANOTAÇÃO
                Action::make('view')
                    ->label('Visualizar')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn (MedicalRecord $record) => $record->title)
                    ->modalSubmitAction(false)
                    ->schema([
                        TextInput::make('title')
                            ->label('Título')
                            ->disabled(),

                        Select::make('appointment_id')
                            ->label('Consulta')
                            ->options(fn () => $this->appointmentOptions())
                            ->disabled(),

                        RichEditor::make('content')
                            ->label('Anotação clínica')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->fillForm(fn (MedicalRecord $record): array => [
                        'title' => $record->title,
                        'appointment_id' => $record->appointment_id,
                        'content' => $record->content,
                    ]),

                // EDITAR ANOTAÇÃO
                EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        Select::make('appointment_id')
                            ->label('Consulta')
                            ->options(fn () => $this->appointmentOptions())
                            ->searchable()
                            ->nullable(),

                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),

                        RichEditor::make('content')
                            ->label('Anotação clínica')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->mutateDataUsing(function (array $data): array {

                        if (
                            !empty($data['appointment_id']) &&
                            !Appointment::query()
                                ->whereKey($data['appointment_id'])
                                ->where('patient_id', $this->patientId)
                                ->exists()
                        ) {
                            throw ValidationException::withMessages([
                                'appointment_id' =>
                                    'A consulta não pertence a este paciente.',
                            ]);
                        }

                        // Impede alterações na identidade do registro
                        unset(
                            $data['patient_id'],
                            $data['user_id']
                        );

                        return $data;
                    }),

                // EXCLUIR ANOTAÇÃO
                DeleteAction::make()
                    ->label('Excluir')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Excluir anotação')
                    ->modalDescription(
                        'Tem certeza que deseja excluir esta anotação?'
                    )
                    ->modalSubmitActionLabel('Sim, excluir'),

            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10, 25]);
    }

    private function appointmentOptions(): array
    {
        return Appointment::query()
            ->where('patient_id', $this->patientId)
            ->orderByDesc('date')
            ->get()
            ->mapWithKeys(fn (Appointment $appointment) => [
                $appointment->id =>
                    'Consulta #' . $appointment->id .
                    ' - ' . $appointment->date,
            ])
            ->toArray();
    }
}