<?php

namespace App\Filament\Resources\DoctorSchedules\Pages;

use App\Filament\Resources\DoctorSchedules\DoctorScheduleResource;
use App\Models\WeekDay;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateDoctorSchedule extends CreateRecord
{
    protected static string $resource = DoctorScheduleResource::class;

    protected static bool $canCreateAnother = false;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl('index')),
        ];
    }

    protected function afterCreate(): void
    {
        // After creating the DoctorSession, we need to create the WeekDay records
        $data = $this->data;
        $doctorSession = $this->record;

        // Use a transaction to ensure data consistency
        DB::transaction(function () use ($data, $doctorSession) {
            // Create time slots for each day
            $this->createTimeSlotsForDay($data, 'monday_time_slots', 1, $doctorSession, 'monday_');
            $this->createTimeSlotsForDay($data, 'tuesday_time_slots', 2, $doctorSession, 'tuesday_');
            $this->createTimeSlotsForDay($data, 'wednesday_time_slots', 3, $doctorSession, 'wednesday_');
            $this->createTimeSlotsForDay($data, 'thursday_time_slots', 4, $doctorSession, 'thursday_');
            $this->createTimeSlotsForDay($data, 'friday_time_slots', 5, $doctorSession, 'friday_');
            $this->createTimeSlotsForDay($data, 'saturday_time_slots', 6, $doctorSession, 'saturday_');
            $this->createTimeSlotsForDay($data, 'sunday_time_slots', 0, $doctorSession, 'sunday_');
        });
    }

    private function createTimeSlotsForDay(array $data, string $slotKey, int $dayOfWeek, $doctorSession, string $prefix): void
    {
        if (isset($data[$slotKey]) && is_array($data[$slotKey])) {
            foreach ($data[$slotKey] as $slot) {

                $startTime = $slot[$prefix . 'start_time'];
                $endTime = $slot[$prefix . 'end_time'];

                if (! $startTime || ! $endTime) {
                    continue;
                }

                $start = Carbon::createFromFormat('H:i', $startTime);
                $end = Carbon::createFromFormat('H:i', $endTime);

                WeekDay::create([
                    'doctor_id' => $doctorSession->doctor_id,
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

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('messages.flash.schedule_crete');
    }

    public function toggleDay(string $day): void
    {
        $current = data_get($this->data, "available.$day", false);

        data_set($this->data, "available.$day", ! $current);

        $this->form->fill($this->data);
    }
}
