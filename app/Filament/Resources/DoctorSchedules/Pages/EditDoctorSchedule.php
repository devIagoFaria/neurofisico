<?php

namespace App\Filament\Resources\DoctorSchedules\Pages;

use App\Filament\Resources\DoctorSchedules\DoctorScheduleResource;
use App\Models\ClinicSchedule;
use App\Models\DoctorSession;
use App\Models\WeekDay;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EditDoctorSchedule extends EditRecord
{
    protected static string $resource = DoctorScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl('index')),
        ];
    }

    public function getTitle(): string
    {
        return __('messages.doctor_session.edit');
    }

    // Load the related WeekDay data for editing
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Get the doctor session record
        $doctorSession = DoctorSession::with('sessionWeekDays')->find($this->record->id);

        if ($doctorSession) {
            // Set the basic doctor session data
            $data['doctor_id'] = $doctorSession->doctor_id;
            $data['session_gap'] = $doctorSession->session_gap;
            $data['session_meeting_time'] = $doctorSession->session_meeting_time;

            // Group the session week days by day of week
            $weekDays = $doctorSession->sessionWeekDays->groupBy('day_of_week');

            // Populate time slots for each day
            foreach ($weekDays as $dayOfWeek => $slots) {
                // Check if clinic schedule exists for this day
                if (!ClinicSchedule::where('day_of_week', $dayOfWeek)->exists()) {
                    continue; // Skip if no clinic schedule exists for this day
                }

                $dayName = match ($dayOfWeek) {
                    1 => 'monday',
                    2 => 'tuesday',
                    3 => 'wednesday',
                    4 => 'thursday',
                    5 => 'friday',
                    6 => 'saturday',
                    0 => 'sunday',
                    default => null
                };

                $slotData = [];
                foreach ($slots as $slot) {
                    $slotData[] = [
                        $dayName . '_start_time' => $slot->start_time,
                        $dayName . '_end_time' => $slot->end_time,
                        // $dayName.'_is_available' => true, // Assuming all loaded slots are available
                    ];
                    $data['available'][$dayName] = true;
                }

                // Map to the appropriate repeater field using dynamic assignment
                $repeaterFieldName = $dayName . '_time_slots';
                $data[$repeaterFieldName] = $slotData;
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->data;
        $session = $this->record;

        DB::transaction(function () use ($data, $session) {

            WeekDay::where('doctor_session_id', $session->id)->delete();

            foreach (
                [
                    'monday' => 1,
                    'tuesday' => 2,
                    'wednesday' => 3,
                    'thursday' => 4,
                    'friday' => 5,
                    'saturday' => 6,
                    'sunday' => 0,
                ] as $day => $dayNum
            ) {

                // Check if clinic schedule exists for this day before saving
                if (ClinicSchedule::where('day_of_week', $dayNum)->exists() && isset($data['available'][$day]) && $data['available'][$day] === true) {
                    $slotKey = "{$day}_time_slots";
                    $this->createTimeSlotsForDay($data, $slotKey, $dayNum, $session, "{$day}_");
                }
            }
        });
    }

    private function createTimeSlotsForDay(array $data, string $slotKey, int $dayOfWeek, $session, string $prefix): void
    {
        if (empty($data[$slotKey]) || ! is_array($data[$slotKey])) {
            return;
        }

        foreach ($data[$slotKey] as $slot) {

            $startTime = $slot[$prefix . 'start_time'] ?? null;
            $endTime = $slot[$prefix . 'end_time'] ?? null;

            if (! $startTime || ! $endTime) {
                continue;
            }

            $start = Carbon::createFromFormat('H:i', $startTime);
            $end = Carbon::createFromFormat('H:i', $endTime);

            WeekDay::create([
                'doctor_id' => $session->doctor_id,
                'doctor_session_id' => $session->id,
                'day_of_week' => $dayOfWeek,
                'start_time' => $start->format('H:i'),
                'end_time' => $end->format('H:i'),
                'start_time_type' => '',
                'end_time_type' => '',
            ]);
        }
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('messages.flash.schedule_update');
    }

    public function toggleDay(string $day): void
    {
        $current = data_get($this->data, "available.$day", false);

        data_set($this->data, "available.$day", ! $current);

        $this->form->fill($this->data);
    }
}
