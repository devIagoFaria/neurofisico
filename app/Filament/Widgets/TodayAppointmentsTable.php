<?php

namespace App\Filament\Widgets;

use App\Filament\Tables\Columns\CustomIdentityColumn;
use App\Models\Appointment;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Carbon\Carbon;

class TodayAppointmentsTable extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Appointment::query()
                    ->with(['doctor.user', 'patient.user'])
                    ->whereDate('date', today())
                    ->whereNotIn('status', [Appointment::CANCELLED])
                    ->limit(5)
            )->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->columns([
                CustomIdentityColumn::make('patient')
                    ->avatar(fn($record) => $record->patient->user->profile_image ?? null)
                    ->html()
                    ->primary(fn($record) => $record->patient->user?->full_name ?? ($record->patient->user->first_name . ' ' . $record->patient->user->last_name ?? 'N/A'))
                    ->label(__('messages.appointment.patient'))
                    ->color('#2b7fff')
                    ->toggleable(false)
                    ->secondary(fn($record) => $record->patient->user?->email ?? 'N/A')
                    ->size('md')
                    ->searchable(false)
                    ->sortable(false),
                CustomIdentityColumn::make('doctor')
                    ->avatar(fn($record) => $record->doctor->user->profile_image ?? null)
                    ->html()
                    ->primary(fn($record) => $record->doctor->user?->full_name ?? ($record->doctor->user->first_name . ' ' . $record->doctor->user->last_name ?? 'N/A'))
                    ->label(__('messages.doctor.doctor'))
                    ->color('#2b7fff')
                    ->toggleable(false)
                    ->secondary(fn($record) => $record->doctor->user?->email ?? 'N/A')
                    ->size('md')
                    ->searchable(false)
                    ->sortable(false),
                TextColumn::make('date')
                    ->label(__('messages.appointment.time'))
                    ->formatStateUsing(function ($record) {
                        return $record->from_time . ' ' . $record->from_time_type . ' - ' . $record->to_time . ' ' . $record->to_time_type;
                    })
                    ->alignCenter()
                    ->badge(),
            ])
            ->header(fn () => view('filament.widgets.today-appointments-table-header', [
                'title' => __('messages.patient_dashboard.today_appointments'),
            ]))
            ->emptyStateHeading(__('messages.custom_table_message.no_appointment'))
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
