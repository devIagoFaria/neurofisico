<?php

namespace App\Filament\Resources\Appointments\Pages;

use Carbon\Carbon;
use App\Models\Setting;
use App\Models\Prescription;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\Grid;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use App\Filament\Resources\Appointments\AppointmentResource;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;

class ViewAppointmentsPrescription extends ViewRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label(__('messages.prescription.print_prescription'))
                ->color('success')
                ->url(fn($record) => route('prescriptions.pdf', $record->id))
                ->openUrlInNewTab(),
            EditAction::make()
                ->url(fn ($record) => AppointmentResource::getUrl(
                        'prescription-edit',
                        ['record' => $this->record->id]
                    )),
            backActionResource(static::$resource::getUrl('view', ['record' => $this->record->appointment_id])),
        ];
    }

    public function getTitle(): string | Htmlable
    {
        return __('messages.prescription.prescription_details');
    }
    public function getHeading(): string
    {
        return __('messages.prescription.prescription_details');
    }

    protected function resolveRecord($key): Model
    {
        return Prescription::with([
            'doctor.user',
            'patient.user',
            'getMedicine.medicines',
        ])->findOrFail($key);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(3)->schema([

                            Grid::make(1)
                                ->schema([
                                    SpatieMediaLibraryImageEntry::make('logo')
                                        ->hiddenLabel()
                                        ->collection(Setting::LOGO)
                                        ->disk(config('app.media_disk'))
                                        ->model(
                                            Setting::where('key', 'logo')->first()
                                            ?? new Setting(['key' => 'logo'])
                                        )
                                        ->imageHeight(80)
                                        ->imageWidth(80),

                                    TextEntry::make('doctor.user.full_name')
                                        ->hiddenLabel()
                                        ->weight('bold')
                                        ->size('lg')
                                        ->extraAttributes(['class' => 'mt-3']),
                                ])
                                ->columnSpan(1),

                            Grid::make(1)
                                ->schema([
                                    TextEntry::make('patient.user.full_name')
                                        ->label(__('messages.web.patient_name') .':'),

                                    TextEntry::make('created_at')
                                        ->label(__('messages.appointment.date') .':')
                                        ->formatStateUsing(
                                            fn($state) =>
                                            Carbon::parse($state)->format('d/m/Y')
                                        ),

                                    TextEntry::make('patient.user.dob')
                                        ->label(__('messages.common.age') .':')
                                        ->formatStateUsing(
                                            fn($state) =>
                                            $state
                                            ? Carbon::parse($state)->age . ' ' . __('messages.common.years')
                                            : __('messages.common.n/a')
                                        ),
                                ])
                                ->columnSpan(1),

                            Grid::make(1)
                                ->schema([
                                    TextEntry::make('doctor.user.address')
                                    ->hiddenLabel()
                                    ->formatStateUsing(function ($record) {

                                        $address = $record->doctor->user->address;
                                        if (
                                            ! $address ||
                                            (
                                                empty($address->address1) &&
                                                empty($address->address2) &&
                                                empty($address->city)
                                            )
                                        ) {
                                            return __('messages.common.n/a');
                                        }

                                        $lines = [];

                                        if ($address->address1) {
                                            $lines[] = e($address->address1);
                                        }

                                        if ($address->address2) {
                                            $lines[] = e($address->address2);
                                        }

                                        $cityZip = trim(
                                            ($address->city->name ?? '') .
                                            ($address->city?->name ? ', ' . $address->postal_code : '')
                                        );

                                        if ($cityZip) {
                                            $lines[] = e($cityZip);
                                        }

                                        if ($record->doctor->user?->contact) {
                                            $lines[] = '<span class="">' .
                                                e($record->doctor->user->contact) .
                                                '</span>';
                                        }

                                        if ($record->doctor->user?->email) {
                                            $lines[] = '<span class="">' .
                                                e($record->doctor->user->email) .
                                                '</span>';
                                        }

                                        return new HtmlString(implode('<br>', $lines));
                                    })
                                    ->columnSpan(1)
                                    ->extraAttributes(['class' => 'text-sm  mt-2']),
                                ])
                        ]),
                    ])
                    ->columnSpanFull(),

                Section::make()
                    ->schema([
                        Grid::make(3)->schema([

                            TextEntry::make('problem_description')
                                ->label(__('messages.prescription.problem') .':')
                                ->default(__('messages.common.n/a'))
                                ->color('gray'),

                            TextEntry::make('test')
                                ->label(__('messages.prescription.test') .':')
                                ->default(__('messages.common.n/a'))
                                ->color('gray'),

                            TextEntry::make('advice')
                                ->label(__('messages.prescription.advice') .':')
                                ->default(__('messages.common.n/a'))
                                ->color('gray'),
                        ]),
                    ])
                    ->columnSpanFull(),

                Section::make(__('messages.prescription.rx'))
                    ->schema([
                        RepeatableEntry::make('getMedicine')
                            ->hiddenLabel()
                            ->schema([

                                TextEntry::make('medicines.0.name')
                                    ->label(__('messages.prescription.medicine_name'))
                                    ->weight('medium'),

                                TextEntry::make('time')
                                    ->label(__('messages.medicine.dosage'))
                                    ->formatStateUsing(
                                        fn($state) => $state == 0 ? '(' . __('messages.prescription.after meal') . ')' : '(' . __('messages.prescription.before meal') . ')'
                                    ),

                                TextEntry::make('day')
                                    ->label(__('messages.prescription.duration'))
                                    ->formatStateUsing(fn($state) => $state . ' Day'),

                                TextEntry::make('dose_interval')
                                    ->label(__('messages.medicine_bills.dose_interval'))
                                    ->formatStateUsing(
                                        fn($state) =>
                                        Prescription::DOSE_INTERVAL[$state] ?? '-'
                                    ),
                            ])
                            ->columns(4),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
