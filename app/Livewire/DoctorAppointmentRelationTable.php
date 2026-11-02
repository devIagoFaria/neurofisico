<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Doctor;
use Filament\Tables\Table;
use App\Models\Appointment;
use Filament\Actions\ViewAction;
use Filament\Widgets\TableWidget;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Illuminate\Support\Facades\Route;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use App\Filament\Resources\Appointments\AppointmentResource;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class DoctorAppointmentRelationTable extends TableWidget
{
    public $record;
    public $id;
    public function mount()
    {
        $this->id = Route::current()->parameter('record');
    }
    public function GetRecord()
    {
        $doctorId = $this->record->doctor?->id;

        return Appointment::query()
            ->with(['patient.user'])
            ->where('doctor_id', $doctorId)
            ->latest();
    }
    public function table(Table $table): Table
    {
        return $table
            ->query($this->GetRecord())
            ->heading('')
            ->searchable(false)
            ->paginated([5, 10])
            ->columns([
                SpatieMediaLibraryImageColumn::make('patient.user.profile')
                    ->label(__('messages.appointment.patient'))
                    ->circular()
                    ->defaultImageUrl(function ($record) {
                        if (! $record->patient->user->hasMedia(User::PROFILE)) {
                            return getUserImageInitial($record->id, $record->patient->user->full_name);
                        }
                    })
                    ->collection(User::PROFILE)
                    ->disk(config('app.media_disk'))
                    ->width(50)->imageHeight(50),
                TextColumn::make('patient.user.full_name')
                    ->label('')
                    ->html()
                    ->color('primary')
                    ->weight(FontWeight::SemiBold)
                    ->description(fn ($record) => $record->patient->user->email ?? __('messages.common.n/a'))
                    ->searchable(['first_name', 'last_name', 'email']),

                TextColumn::make('date')
                    ->label(__('messages.appointment.appointment_date'))
                    ->formatStateUsing(function ($record) {
                        return new HtmlString(
                            '<div class="text-center">'
                            .Carbon::parse($record->from_time)->format('g:i A')
                            .' - '
                            .Carbon::parse($record->to_time)->format('g:i A')
                            .'<br>'
                            .Carbon::parse($record->date)->format('d M Y')
                            .'</div>'
                        );
                    })
                    ->html()
                    ->badge(),
            ])
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->deferFilters(false)
            ->recordUrl(null)
            ->filters([
                SelectFilter::make('status')
                    ->label(__('messages.web.status'))
                    ->native(false)
                    ->options([
                        Appointment::ALL => __('messages.doctor_session.all'),
                        Appointment::BOOKED => __('messages.appointment.booked'),
                        Appointment::CHECK_IN => __('messages.appointment.check_in'),
                        Appointment::CHECK_OUT => __('messages.appointment.check_out'),
                        Appointment::CANCELLED => __('messages.appointment.cancelled'),
                    ]),
                DateRangeFilter::make('date')
                    ->label(__('messages.appointment.date'))
                    ->placeholder(__('messages.common.pick_date_range'))
                    ->startDate(Carbon::now()->startOfWeek()->subDay())
                    ->endDate(Carbon::now()->endOfWeek()->subDay()),
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->url(fn ($record) =>
                        AppointmentResource::getUrl('view', ['record' => $record->id])
                    ),
                DeleteAction::make()
                    ->iconButton(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     //
                // ]),
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_appointment'));
    }
}
