<?php

namespace App\Filament\Doctor\Pages;

use App\Enums\DoctorNavigationSort;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Actions\Action;
use App\Models\ClinicSchedule;
use App\Models\Doctor;
use App\Models\DoctorSession;
use App\Models\WeekDay;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class MySchedule extends Page implements HasForms
{
    use InteractsWithForms;
    protected string $view = 'filament.doctor.pages.my-schedule';
    protected static string|BackedEnum|null $navigationIcon = Phosphor::CalendarBlank;
    protected static ?int $navigationSort = DoctorNavigationSort::MY_SCHEDULE->value;
    public static function getNavigationLabel(): string
    {
        return (__('messages.doctor_session.my_schedule'));
    }

    public ?array $data = [];

    public function mount(): void
    {
        $authUser = Auth::user();
        $formData = [];

        if (in_array('doctor', $authUser->roles->pluck('name')->toArray())) {
            $doctor = $authUser->doctor;

            if ($doctor) {
                $doctorSession = DoctorSession::with('sessionWeekDays')->where('doctor_id', $doctor->id)->first();

                if ($doctorSession) {
                    $formData['doctor_id'] = $doctorSession->doctor_id;
                    $formData['session_gap'] = (string) $doctorSession->session_gap;
                    $formData['session_meeting_time'] = (string) $doctorSession->session_meeting_time;

                    $weekDays = $doctorSession->sessionWeekDays->groupBy('day_of_week');

                    foreach ($weekDays as $dayOfWeek => $slots) {
                        if (!ClinicSchedule::where('day_of_week', $dayOfWeek)->exists()) {
                            continue;
                        }

                        $dayName = match ((int)$dayOfWeek) {
                            1 => 'monday',
                            2 => 'tuesday',
                            3 => 'wednesday',
                            4 => 'thursday',
                            5 => 'friday',
                            6 => 'saturday',
                            0 => 'sunday',
                            default => null
                        };

                        if (!$dayName) continue;

                        $slotData = [];
                        foreach ($slots as $slot) {
                            $slotData[] = [
                                $dayName . '_start_time' => $slot->start_time . ' ' . $slot->start_time_type,
                                $dayName . '_end_time' => $slot->end_time . ' ' . $slot->end_time_type,
                            ];
                        }

                        $formData['available'][$dayName] = !empty($slotData);
                        $formData[$dayName . '_time_slots'] = $slotData;
                    }
                } else {
                    $formData['doctor_id'] = $doctor->id;
                }
            }
        }

        $this->form->fill($formData);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                Hidden::make('doctor_id'),

                                Select::make('session_gap')
                                    ->label(__('messages.doctor_session.session_gap') . ':')
                                    ->options([
                                        '5' =>  '5' . ' ' . __('messages.common.minutes'),
                                        '10' => '10' . ' ' . __('messages.common.minutes'),
                                        '15' => '15' . ' ' . __('messages.common.minutes'),
                                        '20' => '20' . ' ' . __('messages.common.minutes'),
                                        '25' => '25' . ' ' . __('messages.common.minutes'),
                                        '30' => '30' . ' ' . __('messages.common.minutes'),
                                        '45' => '45' . ' ' . __('messages.common.minutes'),
                                        '60' => '1' . ' ' . __('messages.common.hour'),
                                    ])
                                    ->native(false)
                                    ->searchable()
                                    ->required(),

                                Select::make('session_meeting_time')
                                    ->label(__('messages.doctor_session.session_meeting_time') . ':')
                                    ->options([
                                        '5' =>  '5' . ' ' . __('messages.common.minutes'),
                                        '10' => '10' . ' ' . __('messages.common.minutes'),
                                        '15' => '15' . ' ' . __('messages.common.minutes'),
                                        '20' => '20' . ' ' . __('messages.common.minutes'),
                                        '25' => '25' . ' ' . __('messages.common.minutes'),
                                        '30' => '30' . ' ' . __('messages.common.minutes'),
                                        '45' => '45' . ' ' . __('messages.common.minutes'),
                                        '60' => '1' . ' ' . __('messages.common.hour'),
                                        '90' => '1.5' . ' ' . __('messages.common.hour'),
                                        '120' => '2' . ' ' . __('messages.common.hour'),
                                    ])
                                    ->native(false)
                                    ->searchable()
                                    ->required(),
                            ]),
                    ])->columnSpanFull(),

                Section::make(__('messages.appointment.time_slot'))
                    ->schema([
                        Grid::make()
                            ->columns(2)
                            ->schema($this->getTimeSlotRepeaterSchema()
                        ),
                    ])
                    ->columnSpanFull(),
            ])->statePath('data');
    }

    private function getTimeSlotRepeaterSchema(): array
    {
        $days = [
            ['name' => 'monday', 'dayOfWeek' => 1],
            ['name' => 'tuesday', 'dayOfWeek' => 2],
            ['name' => 'wednesday', 'dayOfWeek' => 3],
            ['name' => 'thursday', 'dayOfWeek' => 4],
            ['name' => 'friday', 'dayOfWeek' => 5],
            ['name' => 'saturday', 'dayOfWeek' => 6],
            ['name' => 'sunday', 'dayOfWeek' => 0],
        ];

        $schema = [];

        foreach ($days as $day) {
            $schema[] = Hidden::make("available.{$day['name']}")
                ->default(function () use ($day) {
                    return ClinicSchedule::where('day_of_week', $day['dayOfWeek'])->exists();
                })
                ->hiddenLabel();

            $schema[] = $this->getDayRepeater($day['name'], $day['dayOfWeek']);
        }

        return $schema;
    }

    private function getDayRepeater(string $dayName, int $dayOfWeek): Repeater
    {
        $otherDays = [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday'
        ];

        $targetDays = array_filter($otherDays, fn($d) => $d !== $dayName);

        return Repeater::make("{$dayName}_time_slots")
            ->label(function ($get) use ($dayName) {
                $clinicScheduleExists = ClinicSchedule::where('day_of_week', match ($dayName) {
                    'monday' => 1,
                    'tuesday' => 2,
                    'wednesday' => 3,
                    'thursday' => 4,
                    'friday' => 5,
                    'saturday' => 6,
                    'sunday' => 0
                })->exists();

                $checked = $get("available.{$dayName}") ? 'checked' : '';
                $labelText = __('messages.doctor_session.' . $dayName);
                $hasSlots = count($get("{$dayName}_time_slots") ?? []) > 0;
                $disabled = ($clinicScheduleExists && $hasSlots) ? '' : 'disabled';

                return new HtmlString(
                    '<label class="flex items-center gap-2">
                        <input type="checkbox"
                            wire:click="toggleDay(\'' . $dayName . '\')"
                            class="h-4 w-4"
                            ' . $checked . '
                            ' . $disabled . '
                        />
                        <span>' . e($labelText) . '</span>
                    </label>'
                );
            })
            ->schema([
                Select::make("{$dayName}_start_time")
                    ->options(function (callable $get) use ($dayOfWeek) {
                        $clinicSchedule = ClinicSchedule::where('day_of_week', $dayOfWeek)->first();
                        if (! $clinicSchedule) {
                            return [];
                        }

                        $gap = $get('../../session_gap') ?? 15;

                        return getSlotByGap(
                            $clinicSchedule->start_time,
                            $clinicSchedule->end_time,
                            $gap,
                        );
                    })
                    ->default(function () use ($dayOfWeek) {
                        $clinicSchedule = ClinicSchedule::where('day_of_week', $dayOfWeek)->first();
                        return $clinicSchedule ? $clinicSchedule->start_time : null;
                    })
                    ->native(false)
                    ->searchable()
                    ->required()
                    ->columnSpan(5),
                Select::make("{$dayName}_end_time")
                    ->options(function (callable $get) use ($dayOfWeek) {
                        $clinicSchedule = ClinicSchedule::where('day_of_week', $dayOfWeek)->first();
                        if (! $clinicSchedule) {
                            return [];
                        }

                        $gap = $get('../../session_gap') ?? 15;

                        return getSlotByGap(
                            $clinicSchedule->start_time,
                            $clinicSchedule->end_time,
                            $gap
                        );
                    })
                    ->default(function () use ($dayOfWeek) {
                        $clinicSchedule = ClinicSchedule::where('day_of_week', $dayOfWeek)->first();
                        return $clinicSchedule ? $clinicSchedule->end_time : null;
                    })
                    ->native(false)
                    ->searchable()
                    ->required()
                    ->columnSpan(5),
            ])
            ->compact()
            ->table([
                TableColumn::make(__('messages.doctor_session.start_time')),
                TableColumn::make(__('messages.doctor_session.end_time')),
            ])
            ->extraAttributes([
                'class' => 'custom-time-slot-repeater',
            ])
            ->columns(1)
            ->collapsible()
            ->defaultItems(function () use ($dayOfWeek) {
                return ClinicSchedule::where('day_of_week', $dayOfWeek)->exists() ? 1 : 0;
            })
            ->hintActions([
                self::cloneDayAction(
                    sourceDay: $dayName,
                    targetDays: $targetDays
                )->hiddenLabel()
                    ->icon(Phosphor::Copy)
                    ->iconSize('md')
                    ->color('gray')
                    ->visible(function (callable $get) use ($dayName) {
                        $isAvailable = $get("available.{$dayName}") ?? false;
                        $slots = $get("{$dayName}_time_slots") ?? [];
                        return $isAvailable && count($slots) > 0;
                    }),
            ])
            ->reorderable(false)
            ->addActionLabel(__('messages.common.add'));
    }

    protected static function cloneDayAction(string $sourceDay, array $targetDays): Action
    {
        return Action::make("copy_{$sourceDay}_slots")
            ->modal()
            ->modalHeading(false)
            ->modalWidth('sm')
            ->schema(
                collect([
                    'monday',
                    'tuesday',
                    'wednesday',
                    'thursday',
                    'friday',
                    'saturday',
                    'sunday',
                ])
                    ->reject(fn($day) => $day === $sourceDay)
                    ->filter(function ($day) {
                        // Map day names to clinic schedule day_of_week values
                        $dayMap = [
                            'sunday' => 0,
                            'monday' => 1,
                            'tuesday' => 2,
                            'wednesday' => 3,
                            'thursday' => 4,
                            'friday' => 5,
                            'saturday' => 6,
                        ];

                        $dayOfWeek = $dayMap[$day] ?? null;

                        return $dayOfWeek !== null && ClinicSchedule::where('day_of_week', $dayOfWeek)->exists();
                    })
                    ->map(
                        fn($day) =>
                        Checkbox::make("clone_to_{$day}")
                            ->label(__('messages.doctor_session.' . $day))
                    )
                    ->values()
                    ->toArray()
            )
            ->action(function (array $data, callable $get, callable $set) use ($sourceDay, $targetDays) {

                $sourceRepeater = "{$sourceDay}_time_slots";
                $sourceSlots = $get($sourceRepeater) ?? [];

                if (empty($sourceSlots)) {
                    return;
                }

                $sourceAvailable = $get("available.{$sourceDay}") ?? false;

                foreach ($targetDays as $day) {
                    if (! ($data["clone_to_{$day}"] ?? false)) {
                        continue;
                    }

                    $clonedSlots = collect($sourceSlots)->map(function ($slot) use ($sourceDay, $day) {
                        $startTime = $slot["{$sourceDay}_start_time"] ?? null;
                        $endTime = $slot["{$sourceDay}_end_time"] ?? null;

                        return [
                            "{$day}_start_time"   => $startTime,
                            "{$day}_end_time"     => $endTime,
                        ];
                    })->values()->toArray();

                    $set("{$day}_time_slots", $clonedSlots);
                    // Also copy the availability state
                    $set("available.{$day}", $sourceAvailable);
                }
            });
    }


    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('messages.common.save'))
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $authUser = Auth::user();

        if (!in_array('doctor', $authUser->roles->pluck('name')->toArray())) {
            return;
        }

        $doctor = $authUser->doctor;

        if (!$doctor) {
            return;
        }

        DB::transaction(function () use ($data, $doctor) {
            $doctorSession = DoctorSession::where('doctor_id', $doctor->id)->first();

            if ($doctorSession) {
                $doctorSession->update([
                    'session_gap' => $data['session_gap'],
                    'session_meeting_time' => $data['session_meeting_time'],
                ]);

                WeekDay::where('doctor_session_id', $doctorSession->id)->delete();
            } else {
                $doctorSession = DoctorSession::create([
                    'doctor_id' => $doctor->id,
                    'session_gap' => $data['session_gap'],
                    'session_meeting_time' => $data['session_meeting_time'],
                ]);
            }

            // Create time slots for each day
            $this->createTimeSlotsForDay($data, 'monday_time_slots', 1, $doctorSession, 'monday_', $doctor->id);
            $this->createTimeSlotsForDay($data, 'tuesday_time_slots', 2, $doctorSession, 'tuesday_', $doctor->id);
            $this->createTimeSlotsForDay($data, 'wednesday_time_slots', 3, $doctorSession, 'wednesday_', $doctor->id);
            $this->createTimeSlotsForDay($data, 'thursday_time_slots', 4, $doctorSession, 'thursday_', $doctor->id);
            $this->createTimeSlotsForDay($data, 'friday_time_slots', 5, $doctorSession, 'friday_', $doctor->id);
            $this->createTimeSlotsForDay($data, 'saturday_time_slots', 6, $doctorSession, 'saturday_', $doctor->id);
            $this->createTimeSlotsForDay($data, 'sunday_time_slots', 0, $doctorSession, 'sunday_', $doctor->id);
        });

        Notification::make()
            ->title(__('messages.flash.schedule_update'))
            ->success()
            ->send();
    }

    private function createTimeSlotsForDay(array $data, string $slotKey, int $dayOfWeek, $doctorSession, string $prefix, int $doctorId): void
    {
        if (!ClinicSchedule::where('day_of_week', $dayOfWeek)->exists()) {
            return;
        }

        $availableKey = str_replace('_time_slots', '', $slotKey);
        $isAvailable = $data['available'][$availableKey] ?? false;

        if (!$isAvailable) {
            return;
        }

        if (isset($data[$slotKey]) && is_array($data[$slotKey])) {
            foreach ($data[$slotKey] as $slot) {
                $startTime = $slot[$prefix . 'start_time'] ?? null;
                $endTime = $slot[$prefix . 'end_time'] ?? null;

                if (!$startTime || !$endTime) {
                    continue;
                }

                $start = Carbon::createFromFormat('H:i', $startTime);
                $end = Carbon::createFromFormat('H:i', $endTime);

                if (!$start || !$end) {
                    continue;
                }

                $clinicSchedule = ClinicSchedule::where('day_of_week', $dayOfWeek)->first();
                if ($clinicSchedule) {
                    $clinicStart = Carbon::parse($clinicSchedule->start_time);
                    $clinicEnd = Carbon::parse($clinicSchedule->end_time);

                    if ($start->lt($clinicStart) || $end->gt($clinicEnd) || $start->gte($end)) {
                        continue;
                    }
                }

                WeekDay::create([
                    'doctor_id' => $doctorId,
                    'doctor_session_id' => $doctorSession->id,
                    'day_of_week' => $dayOfWeek,
                    'start_time' => $start->format('H:i'),
                    'end_time' => $end->format('H:i'),
                    'start_time_type' => '',
                    'end_time_type' => '',
                ]);
            }
        }
    }

    public function toggleDay(string $day): void
    {
        $current = data_get($this->data, "available.$day", false);

        data_set($this->data, "available.$day", ! $current);

        $this->form->fill($this->data);
    }
}
