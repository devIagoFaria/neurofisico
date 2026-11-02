<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DoctorHoliday;
use App\Models\Service;
use App\Models\WeekDay;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class FrontCompatibilityController extends AppBaseController
{
    public function getService(Request $request): JsonResponse
    {
        $doctorId = $request->get('appointmentDoctorId');

        $services = Service::with('serviceDoctors')
            ->whereHas('serviceDoctors', function ($query) use ($doctorId) {
                $query->where('doctor_id', $doctorId)->whereStatus(Service::ACTIVE);
            })
            ->get();

        return $this->sendResponse($services, __('messages.flash.retrieve'));
    }

    public function getCharge(Request $request): JsonResponse
    {
        $charge = Service::find($request->get('chargeId'));

        return $this->sendResponse($charge, __('messages.flash.retrieve'));
    }

    public function getDoctorSession(Request $request): JsonResponse
    {
        $holidayDate = $request->get('date');
        $doctorId = $request->get('adminAppointmentDoctorId');
        $timezoneOffsetMinutes = (int) $request->get('timezone_offset_minutes', 0);

        $doctorHoliday = DoctorHoliday::where('doctor_id', $doctorId)
            ->where('date', $holidayDate)
            ->exists();

        if ($doctorHoliday) {
            return $this->sendError(__('messages.flash.doctor_not_available'));
        }

        $timezoneName = timezone_name_from_abbr('', $timezoneOffsetMinutes * 60, false) ?: config('app.timezone');
        $date = Carbon::createFromFormat('Y-m-d', $request->get('date'));

        $clinicSchedule = \App\Models\ClinicSchedule::where('day_of_week', $date->dayOfWeek)->exists();
        if (!$clinicSchedule) {
            return $this->sendError(__('messages.appointment.clinic_closed'));
        }

        $doctorWeekDaySessions = WeekDay::whereDayOfWeek($date->dayOfWeek)
            ->whereDoctorId($doctorId)
            ->with('doctorSession')
            ->get();

        if ($doctorWeekDaySessions->isEmpty()) {
            App::setLocale(getLogInUser()?->language ?? $request->session()->get('languageName'));

            return $this->sendError(__('messages.flash.no_available_slots'));
        }

        $appointments = Appointment::whereDoctorId($doctorId)
            ->whereIn('status', [Appointment::BOOKED, Appointment::CHECK_IN, Appointment::CHECK_OUT])
            ->get();

        $bookedSlots = [];
        $bookingSlots = [];

        foreach ($appointments as $appointment) {
            if ($appointment->date === $request->get('date')) {
                $bookedSlots[] = $appointment->from_time . ' ' . $appointment->from_time_type . ' - ' . $appointment->to_time . ' ' . $appointment->to_time_type;
            }
        }

        foreach ($doctorWeekDaySessions as $doctorWeekDaySession) {
            date_default_timezone_set($timezoneName);

            $doctorSession = $doctorWeekDaySession->doctorSession;
            $startTime = date('H:i', strtotime($doctorWeekDaySession->full_start_time));
            $endTime = date('H:i', strtotime($doctorWeekDaySession->full_end_time));
            $slots = $this->getTimeSlot($doctorSession->session_meeting_time, $startTime, $endTime);
            $gap = $doctorSession->session_gap;
            $isSameWeekDay = Carbon::now()->dayOfWeek === $date->dayOfWeek && Carbon::now()->isSameDay($date);

            foreach ($slots as $key => $slot) {
                $key--;

                if ($key !== 0) {
                    $slotStartTime = date('g:i A', strtotime('+' . $gap * $key . ' minutes', strtotime($slot[0])));
                    $slotEndTime = date('g:i A', strtotime('+' . $gap * $key . ' minutes', strtotime($slot[1])));

                    if (strtotime($doctorWeekDaySession->full_end_time) < strtotime($slotEndTime)) {
                        break;
                    }

                    if (strtotime($slotStartTime) < strtotime($slotEndTime)) {
                        if (($isSameWeekDay && strtotime($slotStartTime) > strtotime(date('g:i A'))) || ! $isSameWeekDay) {
                            $startTimeOrg = Carbon::parse(date('g:i A', strtotime($slotStartTime)));
                            $slotStartTimeCarbon = Carbon::parse(date('g:i A', strtotime($startTime)));
                            $slotEndTimeCarbon = Carbon::parse(date('g:i A', strtotime($endTime)));

                            if (! $startTimeOrg->between($slotStartTimeCarbon, $slotEndTimeCarbon)) {
                                break;
                            }

                            $formattedSlot = $slotStartTime . ' - ' . $slotEndTime;

                            if (in_array($formattedSlot, $bookingSlots, true)) {
                                break;
                            }

                            $bookingSlots[] = $formattedSlot;
                        }
                    }
                } else {
                    if (($isSameWeekDay && strtotime($slot[0]) > strtotime(date('g:i A'))) || ! $isSameWeekDay) {
                        $formattedSlot = date('g:i A', strtotime($slot[0])) . ' - ' . date('g:i A', strtotime($slot[1]));

                        if (in_array($formattedSlot, $bookingSlots, true)) {
                            break;
                        }

                        $bookingSlots[] = $formattedSlot;
                    }
                }
            }
        }

        return $this->sendResponse([
            'bookedSlot' => ! empty($bookedSlots) ? $bookedSlots : null,
            'slots' => $bookingSlots,
        ], __('messages.flash.retrieve'));
    }

    /**
     * @throws Exception
     */
    protected function getTimeSlot($interval, $startTime, $endTime): array
    {
        $start = new DateTime($startTime);
        $end = new DateTime($endTime);
        $carbonStart = Carbon::createFromFormat('H:i', $startTime);
        $carbonEnd = Carbon::createFromFormat('H:i', $endTime);
        $currentStartTime = $start->format('H:i');
        $currentEndTime = $originalEndTime = $end->format('H:i');
        $i = 0;
        $time = [];

        while (strtotime($currentStartTime) <= strtotime($currentEndTime)) {
            $slotStart = $currentStartTime;
            $slotEnd = date('H:i', strtotime('+' . $interval . ' minutes', strtotime($currentStartTime)));
            $currentStartTime = date('H:i', strtotime('+' . $interval . ' minutes', strtotime($currentStartTime)));

            if (! Carbon::createFromFormat('H:i', $slotStart)->isBetween($carbonStart, $carbonEnd)
                || ! Carbon::createFromFormat('H:i', $slotEnd)->isBetween($carbonStart, $carbonEnd)) {
                break;
            }

            $i++;

            if (strtotime($currentStartTime) <= strtotime($currentEndTime)) {
                $time[$i][] = $slotStart;
                $time[$i][] = $slotEnd;
            }

            if (strtotime($currentStartTime) >= strtotime($originalEndTime) || strtotime($slotStart) >= strtotime($slotEnd)) {
                break;
            }
        }

        return $time;
    }
}
