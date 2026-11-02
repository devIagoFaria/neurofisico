<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\WeekDay;
use Carbon\Carbon;
use DateTime;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        $authUser = Auth::user();
        $loggedInDoctorId = null;
        $loggedInPatientId = null;

        if ($authUser->hasRole('doctor')) {
            $loggedInDoctorId = Doctor::where('user_id', $authUser->id)->value('id');
        }

        if ($authUser->hasRole('patient')) {
            $loggedInPatientId = Patient::where('user_id', $authUser->id)->value('id');
        }

        // Get data for select fields
        $doctors = Doctor::with('user')->get()->pluck('user.full_name', 'id')->toArray();
        $patients = Patient::whereHas('user',fn ($q) => $q->whereNotNull('email_verified_at'))->with('user')->get()->pluck('user.full_name', 'id')->toArray();
        $services = Service::where('status', true)->pluck('name', 'id')->toArray();

        return $schema
            ->columns(1)
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make()
                            ->columns(2)
                            ->components([
                                Hidden::make('doctor_id')
                                    ->default(fn() => $loggedInDoctorId)
                                    ->visible(fn() => Auth::user()->hasRole('doctor'))
                                    ->reactive(),

                                Select::make('doctor_id')
                                    ->label(__('messages.doctor.doctor') . ':')
                                    ->options($doctors)
                                    ->required()
                                    ->searchable()
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('available_slot', null);
                                    })
                                    ->hidden(fn($record) => Auth::user()->hasRole('doctor')),

                                DatePicker::make('date')
                                    ->label(__('messages.appointment.date') . ':')
                                    ->placeholder(__('messages.appointment.date'))
                                    ->required()
                                    ->native(false)
                                    ->minDate(today())
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('available_slot', null);
                                    })
                                    ->seconds(false),


                                    Select::make('service_id')
                                    ->label(__('messages.appointment.service') . ':')
                                    ->options($services)
                                    ->required()
                                    ->searchable()
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                                        $set('available_slot', null);

                                        // Get the service charge and update the charge and payable amount fields
                                        if ($state) {
                                            $service = Service::find($state);
                                            if ($service) {
                                                $charge = $service->charges ?? 0;
                                                $set('charge', $charge);
                                                $set('payable_amount', $charge);
                                            }
                                        } else {
                                            $set('charge', null);
                                            $set('payable_amount', null);
                                        }
                                    }),

                                Hidden::make('patient_id')
                                    ->default(fn() => $loggedInPatientId)
                                    ->visible(fn() => Auth::user()->hasRole('patient'))
                                    ->reactive(),
                                Select::make('patient_id')
                                    ->label(__('messages.appointment.patient') . ':')
                                    ->options($patients)
                                    ->required()
                                    ->searchable()
                                    ->native(false)
                                    ->hidden(fn() => isRole('patient')),

                                ToggleButtons::make('available_slot')
                                    ->label(__('messages.appointment.available_slot') . ':')
                                    ->options(function (Get $get) {
                                        $doctorId = $get('doctor_id');
                                        $date = $get('date');

                                        $slotsData = self::getAllSlots($doctorId, $date);

                                        if (isset($slotsData['is_holiday']) && $slotsData['is_holiday']) {
                                            return [__('messages.appointment.doctor_unavailable') => __('messages.appointment.doctor_unavailable')];
                                        }

                                        if (isset($slotsData['is_clinic_closed']) && $slotsData['is_clinic_closed']) {
                                            return [__('messages.appointment.clinic_closed') => __('messages.appointment.clinic_closed')];
                                        }

                                        // Check if there are no slots available
                                        if (empty($slotsData['all_slots'])) {
                                            return [__('messages.appointment.no_slot_found') => __('messages.appointment.no_slot_found')];
                                        }

                                        // Format all slots for the ToggleButtons component
                                        $formattedSlots = [];
                                        foreach ($slotsData['all_slots'] as $slot) {
                                            $formattedSlots[$slot] = $slot;
                                        }

                                        return $formattedSlots;
                                    })
                                    ->colors(function (Get $get) {
                                        $doctorId = $get('doctor_id');
                                        $date = $get('date');

                                        $slotsData = self::getAllSlots($doctorId, $date);

                                        if (isset($slotsData['is_holiday']) && $slotsData['is_holiday']) {
                                            return [__('messages.appointment.doctor_unavailable') => 'danger'];
                                        }

                                        if (isset($slotsData['is_clinic_closed']) && $slotsData['is_clinic_closed']) {
                                            return [__('messages.appointment.clinic_closed') => 'danger'];
                                        }

                                        // If no slots available, show the message in gray
                                        if (empty($slotsData['all_slots'])) {
                                            return [__('messages.appointment.no_slot_found') => 'gray'];
                                        }

                                        $colors = [];
                                        foreach ($slotsData['all_slots'] as $slot) {
                                            if (in_array($slot, $slotsData['booked_slots'])) {
                                                $colors[$slot] = 'danger'; // Red for booked
                                            } else {
                                                $colors[$slot] = 'success'; // Green for available
                                            }
                                        }

                                        return $colors;
                                    })
                                    ->disableOptionWhen(function ($value, Get $get) {

                                        $slotsData = self::getAllSlots(
                                            $get('doctor_id'),
                                            $get('date')
                                        );

                                        if (isset($slotsData['is_holiday']) && $slotsData['is_holiday']) {
                                            return true;
                                        }

                                        if (isset($slotsData['is_clinic_closed']) && $slotsData['is_clinic_closed']) {
                                            return true;
                                        }

                                        if (empty($slotsData['all_slots'])) {
                                            return true;
                                        }

                                        return in_array($value, $slotsData['booked_slots']);
                                    })
                                    ->required()
                                    ->columns(fn (Get $get) => (
                                        count(self::getAllSlots(
                                            $get('doctor_id'),
                                            $get('date')
                                        )['all_slots']) <= 1
                                            ? 1
                                            : [
                                                'sm' => 1,
                                                'md' => 2,
                                                'lg' => 3,
                                                'xl' => 6,
                                            ]
                                    ))
                                    ->gridDirection('row')
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state && ! in_array($state, [
                                            __('messages.appointment.no_slot_found'),
                                            __('messages.appointment.doctor_unavailable'),
                                            __('messages.appointment.clinic_closed'),
                                        ])) {
                                            $times = explode(' - ', $state);
                                            $set('from_time', $times[0]);
                                            $set('to_time', $times[1]);
                                        }
                                    })
                                    ->columnSpanFull()
                                    ->extraAttributes(function (Get $get) {
                                        $slotsData = self::getAllSlots($get('doctor_id'), $get('date'));
                                        $classes = 'custom-slot-buttons';
                                        if (empty($slotsData['all_slots']) && $get('doctor_id') && $get('date')) {
                                            $classes .= ' full-width-slot';
                                        }
                                        return ['class' => $classes];
                                    }),

                                Hidden::make('from_time'),
                                Hidden::make('to_time'),

                                Textarea::make('description')
                                    ->label(__('messages.appointment.description') . ':')
                                    ->placeholder(__('messages.appointment.description'))
                                    ->columnSpanFull(),


                                // Select::make('payment_type')
                                //     ->label(__('messages.medicine_bills.payment_type') . ':')
                                //     ->options(function () {
                                //         $methods = [];
                                //         if (getSettingValue('manual_payment_enabled')) {
                                //             $methods[Appointment::MANUALLY] = __('messages.payment_method.manually');
                                //         }
                                //         if (getSettingValue('stripe_enable')) {
                                //             $methods[Appointment::STRIPE] = __('messages.payment_method.stripe');
                                //         }
                                //         if (getSettingValue('paystack_enable')) {
                                //             $methods[Appointment::PAYSTACK] = __('messages.payment_method.paystack');
                                //         }
                                //         if (getSettingValue('paypal_enable')) {
                                //             $methods[Appointment::PAYPAL] = __('messages.payment_method.paypal');
                                //         }
                                //         if (getSettingValue('razorpay_enable')) {
                                //             $methods[Appointment::RAZORPAY] =  __('messages.payment_method.razorpay');
                                //         }
                                //         if (getSettingValue('authorize_enable')) {
                                //             $methods[Appointment::AUTHORIZE] = __('messages.payment_method.authorize');
                                //         }
                                //         return $methods;
                                //     })
                                //     ->required()
                                //     ->searchable()
                                //     ->native(false),

                                // TextInput::make('charge')
                                //     ->label(__('messages.appointment.charge') . ':')
                                //     ->placeholder(__('messages.appointment.charge'))
                                //     ->prefix(fn () => getCurrencyIcon())
                                //     ->readOnly()
                                //     ->reactive(),

                                // TextInput::make('add_fees')
                                //     ->label(__('messages.appointment.extra_fees') . ':')
                                //     ->placeholder(__('messages.appointment.extra_fees'))
                                //     ->prefix(fn () => getCurrencyIcon())
                                //     ->numeric()
                                //     ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                                //     ->minValue(0)
                                //     ->visible(fn () => ! auth()->user()->hasRole('patient'))
                                //     ->reactive()
                                //     ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                //         $charge = (float) $get('charge');
                                //         $addFees = (float) $state;
                                //         $total = $charge + $addFees;
                                //         $set('payable_amount', $total);
                                //     }),

                                // TextInput::make('payable_amount')
                                //     ->label(__('messages.appointment.payable_amount') . ':')
                                //     ->placeholder(__('messages.appointment.payable_amount'))
                                //     ->prefix(fn () => getCurrencyIcon())
                                //     ->disabled()
                                //     ->dehydrated()
                                //     ->required(),
                            ]),
                    ]),
            ]);
    }

    /**
     * Get all slots (both available and booked) for a doctor on a specific date
     */
    private static function getAllSlots(?int $doctorId, ?string $date): array
    {
        if (! $doctorId || ! $date) {
            return ['all_slots' => [], 'booked_slots' => []];
        }

        try {
            // Parse the date
            $appointmentDate = Carbon::createFromFormat('Y-m-d', date('Y-m-d', strtotime($date)));

            // Check if doctor is on holiday
            $isHoliday = \App\Models\DoctorHoliday::where('doctor_id', $doctorId)
                ->where('date', $appointmentDate->format('Y-m-d'))
                ->exists();

            if ($isHoliday) {
                return ['all_slots' => [], 'booked_slots' => [], 'is_holiday' => true];
            }

            // Check if clinic is closed on this day
            $clinicSchedule = \App\Models\ClinicSchedule::where('day_of_week', $appointmentDate->dayOfWeek)->exists();
            if (!$clinicSchedule) {
                return ['all_slots' => [], 'booked_slots' => [], 'is_clinic_closed' => true];
            }

            // Get doctor's weekday sessions
            $doctorWeekDaySessions = WeekDay::whereDayOfWeek($appointmentDate->dayOfWeek)
                ->whereDoctorId($doctorId)
                ->with('doctorSession')
                ->get();

            if ($doctorWeekDaySessions->count() == 0) {
                return ['all_slots' => [], 'booked_slots' => []];
            }

            $allSlots = [];
            $bookedSlots = [];

            // Get booked appointments for this doctor and date
            $bookedAppointments = Appointment::whereDoctorId($doctorId)
                ->whereIn('status', [Appointment::BOOKED, Appointment::CHECK_IN, Appointment::CHECK_OUT])
                ->where('date', $appointmentDate->format('Y-m-d'))
                ->get();

            foreach ($bookedAppointments as $appointment) {
                $bookedSlot = $appointment->from_time.' - '.
                             $appointment->to_time;
                $bookedSlots[] = $bookedSlot;
            }

            foreach ($doctorWeekDaySessions as $doctorWeekDaySession) {
                $doctorSession = $doctorWeekDaySession->doctorSession;

                if (! $doctorSession) {
                    continue;
                }

                // Convert 12-hour format to 24-hour format
                $startTime = date('H:i', strtotime($doctorWeekDaySession->full_start_time));
                $endTime = date('H:i', strtotime($doctorWeekDaySession->full_end_time));

                // Generate time slots
                $slots = self::getTimeSlot($doctorSession->session_meeting_time, $startTime, $endTime);
                $gap = (int) $doctorSession->session_gap;

                $isToday = Carbon::now()->isSameDay($appointmentDate);
                $currentTime = Carbon::now();

                foreach ($slots as $key => $slot) {
                    $key--;
                    if ($key != 0) {
                        $slotStartTime = date('H:i', strtotime('+'.$gap * $key.' minutes', strtotime($slot[0])));
                        $slotEndTime = date('H:i', strtotime('+'.$gap * $key.' minutes', strtotime($slot[1])));

                        if (strtotime($doctorWeekDaySession->full_end_time) < strtotime($slotEndTime)) {
                            break;
                        }

                        if (strtotime($slotStartTime) < strtotime($slotEndTime)) {
                            // Check if slot is in the future for today, or any day for other dates
                            $slotStartCarbon = Carbon::parse($slotStartTime);
                            $isValidTime = !$isToday || $slotStartCarbon->gt($currentTime);

                            if ($isValidTime) {
                                $startTimeOrg = Carbon::parse(date('H:i', strtotime($slotStartTime)));
                                $slotStartTimeCarbon = Carbon::parse(date('H:i', strtotime($startTime)));
                                $slotEndTimeCarbon = Carbon::parse(date('H:i', strtotime($endTime)));

                                if (! $startTimeOrg->between($slotStartTimeCarbon, $slotEndTimeCarbon)) {
                                    break;
                                }

                                if (in_array(($slotStartTime.' - '.$slotEndTime), $allSlots)) {
                                    break;
                                }

                                $allSlots[] = $slotStartTime.' - '.$slotEndTime;
                            }
                        }
                    } else {
                        $slotStartCarbon = Carbon::parse(date('H:i', strtotime($slot[0])));
                        $isValidTime = !$isToday || $slotStartCarbon->gt($currentTime);

                        if ($isValidTime) {
                            if (in_array((date('H:i', strtotime($slot[0])).' - '.date('H:i', strtotime($slot[1]))), $allSlots)) {
                                break;
                            }

                            $allSlots[] = date('H:i', strtotime($slot[0])).' - '.date('H:i', strtotime($slot[1]));
                        }
                    }
                }
            }

            return [
                'all_slots' => $allSlots,
                'booked_slots' => $bookedSlots,
            ];
        } catch (\Exception $e) {
            // Return empty arrays if there's an error
            return ['all_slots' => [], 'booked_slots' => []];
        }
    }

    /**
     * Generate time slots based on interval, start time, and end time
     * This replicates the logic from the cloneable project's DoctorSessionController
     */
    private static function getTimeSlot($interval, $start_time, $end_time)
    {
        $start = new DateTime($start_time);
        $end = new DateTime($end_time);
        $carbonStart = Carbon::createFromFormat('H:i', $start_time);
        $carbonEnd = Carbon::createFromFormat('H:i', $end_time);
        $startTime = $start->format('H:i');
        $endTime = $originalEndTime = $end->format('H:i');
        $i = 0;
        $time = [];

        while (strtotime($startTime) <= strtotime($endTime)) {
            $start = $startTime;
            $end = date('H:i', strtotime('+'.$interval.' minutes', strtotime($startTime)));
            $startTime = date('H:i', strtotime('+'.$interval.' minutes', strtotime($startTime)));

            if (! Carbon::createFromFormat('H:i', $start)->isBetween($carbonStart, $carbonEnd) ||
                ! Carbon::createFromFormat('H:i', $end)->isBetween($carbonStart, $carbonEnd)) {
                break;
            }

            $i++;
            if (strtotime($startTime) <= strtotime($endTime)) {
                $time[$i][] = $start;
                $time[$i][] = $end;
            }

            if (strtotime($startTime) >= strtotime($originalEndTime)) {
                break;
            }

            if (strtotime($start) >= strtotime($end)) {
                break;
            }
        }

        return $time;
    }
}
