<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Patients\PatientResource;
use App\Filament\Tables\Columns\CustomIdentityColumn;
use App\Models\Patient;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentPatientsRegistrationTable extends TableWidget
{
    public string $activeTab = 'week';

    protected int | string | array $columnSpan = 'full';

    protected bool $isTableSearchable = false;

    protected function getDataQuery()
    {
        $query = Patient::query()->with('user')->latest('created_at')->limit(5);

        $now = now();

        switch ($this->activeTab) {
            case 'day':
                $query->whereDate('created_at', $now->toDateString());
                break;

            case 'week':
                $query->whereBetween('created_at', [
                    $now->copy()->startOfWeek(),
                    $now->copy()->endOfWeek(),
                ]);
                break;

            case 'month':
                $query->whereMonth('created_at', $now->month)
                    ->whereYear('created_at', $now->year);
                break;
        }

        return $query->limit(5);
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;

        $this->resetTable();
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->getDataQuery())
            ->defaultSort('created_at', 'desc')
            ->searchable(false)
            ->paginated(false)
            ->columns([
                CustomIdentityColumn::make('first_name')
                    ->avatar(fn($record) => $record->user?->profile_image ?? null)
                    ->html()
                    ->primary(fn($record) => $record->user?->full_name ?? ($record->user?->first_name . ' ' . $record->user?->last_name ?? 'N/A'))
                    ->label(__('messages.admin_dashboard.name'))
                    ->color('#2b7fff')
                    ->toggleable(false)
                    ->primaryUrl(fn($record) => PatientResource::getUrl('view', ['record' => $record->user->id]))
                    ->secondary(fn($record) => $record->user?->email ?? 'N/A')
                    ->size('md')
                    ->searchable(false)
                    ->sortable(false),
                TextColumn::make('patient_unique_id')
                    ->label(__('messages.patient.patient_unique_id'))
                    ->badge()
                    ->color('success')
                    ->default(__('messages.common.n/a')),
                TextColumn::make('appointments')
                    ->label(__('messages.doctor_dashboard.total_appointments'))
                    ->getStateUsing(fn($record) => $record->appointments()->count())
                    ->badge()
                    ->color('danger')
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label(__('messages.patient.registered_on'))
                    ->formatStateUsing(fn ($state) => $state->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y \à\s H:i'))
                    ->badge()
                    ->alignCenter(),
            ])
            ->header(fn () => view('filament.widgets.recent-patients-registration-table-header', [
                'activeTab' => $this->activeTab,
                'title' => __('messages.admin_dashboard.recent_patients_registration'),
            ]))
            ->emptyStateHeading(__('messages.custom_table_message.patient_registration'))
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
