<?php

namespace App\Filament\Patient\Widgets;

use App\Filament\Tables\Columns\CustomIdentityColumn;
use App\Models\Appointment;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\HtmlString;

class UpcomingAppointmentsTable extends TableWidget
{
    protected function getTableHeading(): string
    {
        return __('messages.patient_dashboard.upcoming_appointments');
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Appointment::query()
                    ->with(['doctor.user'])
                    ->where('patient_id', auth()->user()->patient->id)
                    ->whereDate('date', '>', today())
                    ->whereNotIn('status', [Appointment::CANCELLED])
                    ->limit(5)
            )
            ->paginated(false)
            ->defaultSort('created_at', 'desc')
            ->columns([
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
                    ->label(__('messages.appointment.date'))
                    ->formatStateUsing(function ($record) {
                        return new HtmlString(
                            '<div class="text-center">'
                                . Carbon::parse($record->date)->format('d M Y') . ' '
                                . Carbon::parse($record->from_time)->format('g:i A')
                                . ' - '
                                . Carbon::parse($record->to_time)->format('g:i A')
                                . '</div>'
                        );
                    })
                    ->html()
                    ->alignCenter()
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_appointment'))
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
