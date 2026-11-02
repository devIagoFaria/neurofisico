<?php

namespace App\Repositories;

use App\Models\Doctor;
use App\Models\LiveConsultation;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\UserZoomCredential;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class LiveConsultationRepository
 */
class LiveConsultationRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'doctor_id',
        'patient_id',
        'consultation_title',
        'consultation_date',
        'consultation_duration_minutes',
        'type',
        'type_number',
        'description',
    ];

    /**
     * Return searchable fields
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return LiveConsultation::class;
    }

    /**
     * @throws BindingResolutionException
     */
    public function store(array $input): bool
    {
        /** @var ZoomRepository $zoomRepo */
        $zoomRepo = App::makeWith(ZoomRepository::class, ['createdBy' => getLogInUserId()]);

        try {
            $input['created_by'] = getLogInUserId();
            $input['consultation_date'];
            $zoom = $zoomRepo->createZoomMeeting($input);
            $input['password'] = $zoom['password'];
            $input['meeting_id'] = $zoom['id'];
            $input['meta'] = $zoom;

            $input['status'] = $input['status'] ?? 0;
            $input['time_zone'] = $input['time_zone'] ?? '';

            $zoomModel = LiveConsultation::create($input);

            $patient = Patient::with('user')->where('id', $input['patient_id'])->first();
            $doctor = Doctor::with('user')->where('id', $input['doctor_id'])->first();

            $patientNotification = Notification::create([
                'title' => 'Your live consultation has been created by '.$doctor->user->full_name.'.',
                'type' => Notification::LIVE_CONSULTATION,
                'user_id' => $patient->user->id,
            ]);

            $doctorNotification = Notification::create([
                'title' => $patient->user->full_name.' live consultation has been booked.',
                'type' => Notification::LIVE_CONSULTATION,
                'user_id' => $doctor->user->id,
            ]);

            return true;
        } catch (Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    public function createUserZoom(array $input)
    {
        try {
            UserZoomCredential::updateOrCreate([
                'user_id' => getLogInUserId(),
            ], [
                'user_id' => getLogInUserId(),
                'zoom_api_key' => $input['zoom_api_key'],
                'zoom_api_secret' => $input['zoom_api_secret'],
            ]);

            return true;
        } catch (Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }
}
