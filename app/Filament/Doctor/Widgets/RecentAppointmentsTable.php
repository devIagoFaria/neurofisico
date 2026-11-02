<?php

namespace App\Filament\Doctor\Widgets;

use App\Filament\Resources\Patients\PatientResource;
use App\Filament\Tables\Columns\CustomIdentityColumn;
use App\Models\Appointment;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class RecentAppointmentsTable extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected bool $isTableSearchable = false;

    public string $activeTab = 'week';

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    protected function getDataQuery()
    {
        $query = Appointment::query()
            ->with(['patient.user'])
            ->where('doctor_id', auth()->user()->doctor->id)
            ->whereStatus(Appointment::BOOKED)
            ->limit(5);

        $now = now();

        switch ($this->activeTab) {
            case 'day':
                $query->whereDate('date', $now->toDateString());
                break;

            case 'week':
                $query->whereBetween('date', [
                    $now->copy()->startOfWeek()->toDateString(),
                    $now->copy()->endOfWeek()->toDateString(),
                ]);
                break;

            case 'month':
                $query->whereMonth('date', $now->month)
                    ->whereYear('date', $now->year);
                break;
        }

        return $query->latest('date');
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
    public function table(Table $table): Table
    {
        return $table
        ->query(fn () => $this->getDataQuery())
        ->paginated(false)
        ->defaultSort('created_at', 'desc')
            ->columns([
                CustomIdentityColumn::make('patient')
                    ->avatar(fn($record) => $record->patient->user->profile_image ?? null)
                    ->html()
                    ->primary(fn($record) => $record->patient->user?->full_name ?? ($record->patient->user->first_name . ' ' . $record->patient->user->last_name ?? 'N/A'))
                    ->label(__('messages.admin_dashboard.name'))
                    ->color('#2b7fff')
                    ->toggleable(false)
                    ->primaryUrl(fn($record) => PatientResource::getUrl('view', ['record' => $record->patient->id]))
                    ->secondary(fn($record) => $record->patient->user?->email ?? 'N/A')
                    ->size('md')
                    ->searchable(false)
                    ->sortable(false),
                TextColumn::make('patient.patient_unique_id')
                    ->label(__('messages.patient.patient_unique_id'))
                    ->badge()
                    ->color('success')
                    ->alignCenter()
                    ->default(__('messages.common.n/a')),
                TextColumn::make('date')
                    ->label(__('messages.appointment.date'))
                    ->formatStateUsing(function ($record) {
                        return new HtmlString(
                            '<div class="text-center">'
                                . Carbon::parse($record->from_time)->format('g:i A')
                                . ' - '
                                . Carbon::parse($record->to_time)->format('g:i A')
                                . '<br>'
                                . Carbon::parse($record->date)->format('d M Y')
                                . '</div>'
                        );
                    })
                    ->html()
                    ->alignCenter()
                    ->badge(),
            ])->header(fn () => view('filament.widgets.recent-patients-registration-table-header', [
                'activeTab' => $this->activeTab,
                'title' => __('messages.doctor_dashboard.recent_appointments'),
            ]))
            ->emptyStateHeading(__('messages.custom_table_message.no_appointment'))
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
