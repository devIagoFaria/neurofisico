<?php

namespace App\Filament\Resources\DoctorSchedules\Schemas;

use App\Models\ClinicSchedule;
use App\Models\Doctor;
use App\Models\DoctorSession;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class DoctorScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                Select::make('doctor_id')
                                    ->label(__('messages.doctor.doctor') . ':')
                                    ->options(function () {
                                        $doctorsWithSessions = DoctorSession::pluck('doctor_id')->toArray();

                                        return Doctor::whereNotIn('id', $doctorsWithSessions)
                                            ->with('user')
                                            ->get()
                                            ->pluck('user.full_name', 'id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->disabledOn('edit')
                                    ->native(false)
                                    ->visible(fn() => ! Auth::user()->hasRole('doctor'))
                                    ->default(fn() => Auth::user()->hasRole('doctor') ? Auth::user()->doctor->id : null)
                                    ->getOptionLabelUsing(fn($value): string => Doctor::find($value)?->user?->full_name ?? ''),

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
                            ->schema([
                                Hidden::make('available.monday')
                                    ->default(function () {
                                        return ClinicSchedule::where('day_of_week', 1)->exists();
                                    })
                                    ->hiddenLabel(),
                                Repeater::make('monday_time_slots')
                                    ->label(function ($get): HtmlString {
                                        // Check if clinic schedule exists for Monday
                                        $clinicScheduleExists = ClinicSchedule::where('day_of_week', 1)->exists();
                                        $checked = $get('available.monday') ? 'checked' : '';
                                        $labelText = __('messages.doctor_session.monday');

                                        // Only enable checkbox if clinic schedule exists AND there are slots
                                        $hasSlots = count($get('monday_time_slots') ?? []) > 0;
                                        $disabled = ($clinicScheduleExists && $hasSlots) ? '' : 'disabled';

                                        return new HtmlString(
                                            '<label class="flex items-center gap-2">
                                                <input
                                                    type="checkbox"
                                                    wire:click="toggleDay(\'monday\')"
                                                    class="h-4 w-4"
                                                    ' . $checked . '
                                                    ' . $disabled . '
                                                />
                                                <span>
                                                    ' . e($labelText) . '
                                                </span>
                                            </label>'
                                        );
                                    })

                                    ->schema([
                                        Select::make('monday_start_time')
                                            ->options(function (callable $get) {
                                                // Get clinic schedule for Monday (day 1)
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 1)->first();

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
                                            ->default(function () {
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 1)->first();
                                                return $clinicSchedule ? $clinicSchedule->start_time : null;
                                            })
                                            ->native(false)
                                            ->searchable()
                                            ->required()
                                            ->columnSpan(5),
                                        Select::make('monday_end_time')

                                            ->options(function (callable $get) {
                                                // Get clinic schedule for Monday (day 1)
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 1)->first();
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
                                            ->default(function () {
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 1)->first();
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
                                    ->columns(1)
                                    ->collapsible()
                                    ->defaultItems(function () {
                                        // Only show default item if clinic schedule exists
                                        return ClinicSchedule::where('day_of_week', 1)->exists() ? 1 : 0;
                                    })
                                    ->hintActions([
                                        self::cloneDayAction(
                                            sourceDay: 'monday',
                                            targetDays: ['tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
                                        )->hiddenLabel()
                                            ->icon(Phosphor::Copy)
                                            ->iconSize('md')
                                            ->color('gray')
                                            ->visible(function (callable $get) {
                                                $isAvailable = $get('available.monday') ?? false;
                                                $slots = $get('monday_time_slots') ?? [];

                                                return $isAvailable && count($slots) > 0;
                                            }),
                                    ])
                                    ->reorderable(false)
                                    ->deletable(false)
                                    ->addActionLabel(__('messages.common.add')),

                                Hidden::make('available.tuesday')
                                    ->default(function () {
                                        return ClinicSchedule::where('day_of_week', 2)->exists();
                                    })
                                    ->hiddenLabel(),
                                Repeater::make('tuesday_time_slots')
                                    ->label(function ($get): HtmlString {
                                        // Check if clinic schedule exists for Tuesday
                                        $clinicScheduleExists = ClinicSchedule::where('day_of_week', 2)->exists();
                                        $checked = $get('available.tuesday') ? 'checked' : '';
                                        $labelText = __('messages.doctor_session.tuesday');

                                        // Only enable checkbox if clinic schedule exists AND there are slots
                                        $hasSlots = count($get('tuesday_time_slots') ?? []) > 0;
                                        $disabled = ($clinicScheduleExists && $hasSlots) ? '' : 'disabled';

                                        return new HtmlString(
                                            '<label class="flex items-center gap-2">
                                                <input type="checkbox"
                                                    wire:click="toggleDay(\'tuesday\')"
                                                    class="h-4 w-4"
                                                    ' . $checked . '
                                                    ' . $disabled . '
                                                />
                                                <span>' . $labelText . '</span>
                                            </label>'
                                        );
                                    })
                                    ->schema([
                                        Select::make('tuesday_start_time')

                                            ->options(function (callable $get) {
                                                // Get clinic schedule for Tuesday (day 2)
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 2)->first();
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
                                            ->default(function () {
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 2)->first();
                                                return $clinicSchedule ? $clinicSchedule->start_time : null;
                                            })
                                            ->native(false)
                                            ->searchable()
                                            ->required()
                                            ->columnSpan(5),
                                        Select::make('tuesday_end_time')

                                            ->options(function (callable $get) {
                                                // Get clinic schedule for Tuesday (day 2)
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 2)->first();
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
                                            ->default(function () {
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 2)->first();
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
                                    ->columns(1)
                                    ->collapsible()
                                    ->defaultItems(function () {
                                        // Only show default item if clinic schedule exists
                                        return ClinicSchedule::where('day_of_week', 2)->exists() ? 1 : 0;
                                    })
                                    ->hintActions([
                                        self::cloneDayAction(
                                            sourceDay: 'tuesday',
                                            targetDays: ['monday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
                                        )->hiddenLabel()
                                            ->icon(Phosphor::Copy)
                                            ->iconSize('md')
                                            ->color('gray')
                                            ->visible(function (callable $get) {
                                                $isAvailable = $get('available.tuesday') ?? false;
                                                $slots = $get('tuesday_time_slots') ?? [];

                                                return $isAvailable && count($slots) > 0;
                                            }),
                                    ])
                                    ->reorderable(false)
                                    ->addActionLabel(__('messages.common.add')),

                                Hidden::make('available.wednesday')
                                    ->default(function () {
                                        return ClinicSchedule::where('day_of_week', 3)->exists();
                                    })
                                    ->hiddenLabel(),
                                Repeater::make('wednesday_time_slots')
                                    ->label(function ($get): HtmlString {
                                        // Check if clinic schedule exists for Wednesday
                                        $clinicScheduleExists = ClinicSchedule::where('day_of_week', 3)->exists();
                                        $checked = $get('available.wednesday') ? 'checked' : '';
                                        $labelText = __('messages.doctor_session.wednesday');

                                        // Only enable checkbox if clinic schedule exists AND there are slots
                                        $hasSlots = count($get('wednesday_time_slots') ?? []) > 0;
                                        $disabled = ($clinicScheduleExists && $hasSlots) ? '' : 'disabled';

                                        return new HtmlString(
                                            '<label class="flex items-center gap-2">
                                                <input type="checkbox"
                                                    wire:click="toggleDay(\'wednesday\')"
                                                    class="h-4 w-4"
                                                    ' . $checked . '
                                                    ' . $disabled . '
                                                />
                                                <span>' . $labelText . '</span>
                                            </label>'
                                        );
                                    })
                                    ->schema([
                                        Select::make('wednesday_start_time')

                                            ->options(function (callable $get) {
                                                // Get clinic schedule for Wednesday (day 3)
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 3)->first();
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
                                            ->default(function () {
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 3)->first();
                                                return $clinicSchedule ? $clinicSchedule->start_time : null;
                                            })
                                            ->native(false)
                                            ->searchable()
                                            ->required()
                                            ->columnSpan(5),
                                        Select::make('wednesday_end_time')

                                            ->options(function (callable $get) {
                                                // Get clinic schedule for Wednesday (day 3)
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 3)->first();
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
                                            ->default(function () {
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 3)->first();
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
                                    ->columns(1)
                                    ->collapsible()
                                    ->defaultItems(function () {
                                        // Only show default item if clinic schedule exists
                                        return ClinicSchedule::where('day_of_week', 3)->exists() ? 1 : 0;
                                    })
                                    ->hintActions([
                                        self::cloneDayAction(
                                            sourceDay: 'wednesday',
                                            targetDays: ['monday', 'tuesday', 'thursday', 'friday', 'saturday', 'sunday']
                                        )->hiddenLabel()
                                            ->icon(Phosphor::Copy)
                                            ->iconSize('md')
                                            ->color('gray')
                                            ->visible(function (callable $get) {
                                                $isAvailable = $get('available.wednesday') ?? false;
                                                $slots = $get('wednesday_time_slots') ?? [];

                                                return $isAvailable && count($slots) > 0;
                                            }),
                                    ])
                                    ->reorderable(false)
                                    ->deletable(false)
                                    ->addActionLabel(__('messages.common.add')),

                                Hidden::make('available.thursday')
                                    ->default(function () {
                                        return ClinicSchedule::where('day_of_week', 4)->exists();
                                    })
                                    ->hiddenLabel(),
                                Repeater::make('thursday_time_slots')
                                    ->label(function ($get): HtmlString {
                                        // Check if clinic schedule exists for Thursday
                                        $clinicScheduleExists = ClinicSchedule::where('day_of_week', 4)->exists();
                                        $checked = $get('available.thursday') ? 'checked' : '';
                                        $labelText = __('messages.doctor_session.thursday');

                                        // Only enable checkbox if clinic schedule exists AND there are slots
                                        $hasSlots = count($get('thursday_time_slots') ?? []) > 0;
                                        $disabled = ($clinicScheduleExists && $hasSlots) ? '' : 'disabled';

                                        return new HtmlString(
                                            '<label class="flex items-center gap-2">
                                                <input type="checkbox"
                                                    wire:click="toggleDay(\'thursday\')"
                                                    class="h-4 w-4"
                                                    ' . $checked . '
                                                    ' . $disabled . '
                                                />
                                                <span>' . $labelText . '</span>
                                            </label>'
                                        );
                                    })
                                    ->schema([
                                        Select::make('thursday_start_time')

                                            ->options(function (callable $get) {
                                                // Get clinic schedule for Thursday (day 4)
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 4)->first();
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
                                            ->default(function () {
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 4)->first();
                                                return $clinicSchedule ? $clinicSchedule->start_time : null;
                                            })
                                            ->native(false)
                                            ->searchable()
                                            ->required()
                                            ->columnSpan(5),
                                        Select::make('thursday_end_time')

                                            ->options(function (callable $get) {
                                                // Get clinic schedule for Thursday (day 4)
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 4)->first();
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
                                            ->default(function () {
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 4)->first();
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
                                    ->columns(1)
                                    ->collapsible()
                                    ->defaultItems(function () {
                                        // Only show default item if clinic schedule exists
                                        return ClinicSchedule::where('day_of_week', 4)->exists() ? 1 : 0;
                                    })
                                    ->hintActions([
                                        self::cloneDayAction(
                                            sourceDay: 'thursday',
                                            targetDays: ['monday', 'tuesday', 'wednesday', 'friday', 'saturday', 'sunday']
                                        )->hiddenLabel()
                                            ->icon(Phosphor::Copy)
                                            ->iconSize('md')
                                            ->color('gray')
                                            ->visible(function (callable $get) {
                                                $isAvailable = $get('available.thursday') ?? false;
                                                $slots = $get('thursday_time_slots') ?? [];

                                                return $isAvailable && count($slots) > 0;
                                            }),
                                    ])
                                    ->reorderable(false)
                                    ->deletable(false)
                                    ->addActionLabel(__('messages.common.add')),

                                Hidden::make('available.friday')
                                    ->default(function () {
                                        return ClinicSchedule::where('day_of_week', 5)->exists();
                                    })
                                    ->hiddenLabel(),
                                Repeater::make('friday_time_slots')
                                    ->label(function ($get): HtmlString {
                                        // Check if clinic schedule exists for Friday
                                        $clinicScheduleExists = ClinicSchedule::where('day_of_week', 5)->exists();
                                        $checked = $get('available.friday') ? 'checked' : '';
                                        $labelText = __('messages.doctor_session.friday');

                                        // Only enable checkbox if clinic schedule exists AND there are slots
                                        $hasSlots = count($get('friday_time_slots') ?? []) > 0;
                                        $disabled = ($clinicScheduleExists && $hasSlots) ? '' : 'disabled';

                                        return new HtmlString(
                                            '<label class="flex items-center gap-2">
                                                <input type="checkbox"
                                                    wire:click="toggleDay(\'friday\')"
                                                     class="h-4 w-4"
                                                    ' . $checked . '
                                                    ' . $disabled . '
                                                />
                                                <span>' . $labelText . '</span>
                                            </label>'
                                        );
                                    })

                                    ->schema([
                                        Select::make('friday_start_time')

                                            ->options(function (callable $get) {
                                                // Get clinic schedule for Friday (day 5)
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 5)->first();

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
                                            ->default(function () {
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 5)->first();
                                                return $clinicSchedule ? $clinicSchedule->start_time : null;
                                            })
                                            ->native(false)
                                            ->searchable()
                                            ->required()
                                            ->columnSpan(5),
                                        Select::make('friday_end_time')

                                            ->options(function (callable $get) {
                                                // Get clinic schedule for Friday (day 5)
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 5)->first();
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
                                            ->default(function () {
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 5)->first();
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
                                    ->columns(1)
                                    ->collapsible()
                                    ->defaultItems(function () {
                                        // Only show default item if clinic schedule exists
                                        return ClinicSchedule::where('day_of_week', 5)->exists() ? 1 : 0;
                                    })
                                    ->hintActions([
                                        self::cloneDayAction(
                                            sourceDay: 'friday',
                                            targetDays: ['monday', 'tuesday', 'wednesday', 'thursday', 'saturday', 'sunday']
                                        )->hiddenLabel()
                                            ->icon(Phosphor::Copy)
                                            ->iconSize('md')
                                            ->color('gray')
                                            ->visible(function (callable $get) {
                                                $isAvailable = $get('available.friday') ?? false;
                                                $slots = $get('friday_time_slots') ?? [];

                                                return $isAvailable && count($slots) > 0;
                                            }),
                                    ])
                                    ->reorderable(false)
                                    ->deletable(false)
                                    ->addActionLabel(__('messages.common.add')),

                                Hidden::make('available.saturday')
                                    ->default(function () {
                                        return ClinicSchedule::where('day_of_week', 6)->exists();
                                    })
                                    ->hiddenLabel(),
                                Repeater::make('saturday_time_slots')
                                    ->label(function ($get): HtmlString {
                                        // Check if clinic schedule exists for Saturday
                                        $clinicScheduleExists = ClinicSchedule::where('day_of_week', 6)->exists();
                                        $checked = $get('available.saturday') ? 'checked' : '';
                                        $labelText = __('messages.doctor_session.saturday');

                                        // Only enable checkbox if clinic schedule exists AND there are slots
                                        $hasSlots = count($get('saturday_time_slots') ?? []) > 0;
                                        $disabled = ($clinicScheduleExists && $hasSlots) ? '' : 'disabled';

                                        return new HtmlString(
                                            '<label class="flex items-center gap-2">
                                                <input type="checkbox"
                                                    wire:click="toggleDay(\'saturday\')"
                                                     class="h-4 w-4"
                                                    ' . $checked . '
                                                    ' . $disabled . '
                                                />
                                                <span>' . $labelText . '</span>
                                            </label>'
                                        );
                                    })
                                    ->schema([
                                        Select::make('saturday_start_time')

                                            ->options(function (callable $get) {
                                                // Get clinic schedule for Saturday (day 6)
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 6)->first();
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
                                            ->default(function () {
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 6)->first();
                                                return $clinicSchedule ? $clinicSchedule->start_time : null;
                                            })
                                            ->native(false)
                                            ->searchable()
                                            ->required()
                                            ->columnSpan(5),
                                        Select::make('saturday_end_time')

                                            ->options(function (callable $get) {
                                                // Get clinic schedule for Saturday (day 6)
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 6)->first();
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
                                            ->default(function () {
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 6)->first();
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
                                    ->columns(1)
                                    ->collapsible()
                                    ->defaultItems(function () {
                                        // Only show default item if clinic schedule exists
                                        return ClinicSchedule::where('day_of_week', 6)->exists() ? 1 : 0;
                                    })
                                    ->hintActions([
                                        self::cloneDayAction(
                                            sourceDay: 'saturday',
                                            targetDays: ['monday', 'tuesday', 'wednesday', 'friday', 'sunday']
                                        )->hiddenLabel()
                                            ->icon(Phosphor::Copy)
                                            ->iconSize('md')
                                            ->color('gray')
                                            ->visible(function (callable $get) {
                                                $isAvailable = $get('available.saturday') ?? false;
                                                $slots = $get('saturday_time_slots') ?? [];

                                                return $isAvailable && count($slots) > 0;
                                            }),
                                    ])
                                    ->reorderable(false)
                                    ->deletable(false)
                                    ->addActionLabel(__('messages.common.add')),

                                Hidden::make('available.sunday')
                                    ->default(function () {
                                        return ClinicSchedule::where('day_of_week', 0)->exists();
                                    })
                                    ->hiddenLabel(),
                                Repeater::make('sunday_time_slots')
                                    ->label(function ($get): HtmlString {
                                        // Check if clinic schedule exists for Sunday
                                        $clinicScheduleExists = ClinicSchedule::where('day_of_week', 0)->exists();
                                        $checked = $get('available.sunday') ? 'checked' : '';
                                        $labelText = __('messages.doctor_session.sunday');

                                        // Only enable checkbox if clinic schedule exists AND there are slots
                                        $hasSlots = count($get('sunday_time_slots') ?? []) > 0;
                                        $disabled = ($clinicScheduleExists && $hasSlots) ? '' : 'disabled';

                                        return new HtmlString(
                                            '<label class="flex items-center gap-2">
                                                <input type="checkbox"
                                                    wire:click="toggleDay(\'sunday\')"
                                                     class="h-4 w-4"
                                                    ' . $checked . '
                                                    ' . $disabled . '
                                                />
                                                <span>' . $labelText . '</span>
                                            </label>'
                                        );
                                    })
                                    ->schema([
                                        Select::make('sunday_start_time')

                                            ->options(function (callable $get) {
                                                // Get clinic schedule for Sunday (day 0)
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 0)->first();
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
                                            ->default(function () {
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 0)->first();
                                                return $clinicSchedule ? $clinicSchedule->start_time : null;
                                            })
                                            ->native(false)
                                            ->searchable()
                                            ->required()
                                            ->columnSpan(5),
                                        Select::make('sunday_end_time')

                                            ->options(function (callable $get) {
                                                // Get clinic schedule for Sunday (day 0)
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 0)->first();
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
                                            ->default(function () {
                                                $clinicSchedule = ClinicSchedule::where('day_of_week', 0)->first();
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
                                    ->columns(1)
                                    ->collapsible()
                                    ->defaultItems(function () {
                                        // Only show default item if clinic schedule exists
                                        return ClinicSchedule::where('day_of_week', 0)->exists() ? 1 : 0;
                                    })
                                    ->hintActions([
                                        self::cloneDayAction(
                                            sourceDay: 'sunday',
                                            targetDays: ['monday', 'tuesday', 'wednesday', 'friday', 'saturday']
                                        )->hiddenLabel()
                                            ->icon(Phosphor::Copy)
                                            ->iconSize('md')
                                            ->color('gray')
                                            ->visible(function (callable $get) {
                                                $isAvailable = $get('available.sunday') ?? false;
                                                $slots = $get('sunday_time_slots') ?? [];

                                                return $isAvailable && count($slots) > 0;
                                            }),
                                    ])
                                    ->reorderable(false)
                                    ->deletable(false)
                                    ->addActionLabel(__('messages.common.add')),
                            ]),
                    ])->columnSpanFull()
                    ->extraAttributes([
                        'class' => 'custom-time-slot-repeater',
                    ]),
            ]);
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
                    ->reject(fn($day) => $day === $sourceDay) // remove source day
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

                        // Only show days that have clinic schedules
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

                // Get the source day's availability state
                $sourceAvailable = $get("available.{$sourceDay}") ?? false;

                foreach ($targetDays as $day) {
                    if (! ($data["clone_to_{$day}"] ?? false)) {
                        continue;
                    }

                    $clonedSlots = collect($sourceSlots)->map(function ($slot) use ($sourceDay, $day) {
                        return [
                            "{$day}_start_time"   => $slot["{$sourceDay}_start_time"] ?? null,
                            "{$day}_end_time"     => $slot["{$sourceDay}_end_time"] ?? null,
                            "{$day}_is_available" => $slot["{$sourceDay}_is_available"] ?? true,
                        ];
                    })->values()->toArray();

                    $set("{$day}_time_slots", $clonedSlots);
                    // Also copy the availability state
                    $set("available.{$day}", $sourceAvailable);
                }
            });
    }
}
