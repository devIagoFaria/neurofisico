<?php

namespace App\Http\Controllers;

use App\Models\AppointmentGoogleCalendar;
use App\Models\Doctor;
use App\Models\GoogleCalendarIntegration;
use App\Models\GoogleCalendarList;
use App\Models\Patient;
use Google_Client;
use Google_Service_Calendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleCalendarController extends AppBaseController
{
    private ?Google_Client $client = null;

    private function initializeGoogleClient(): void
    {
        $userId = session('google_oauth_user_id') ?? Auth::id();

        $doctor = Doctor::where('user_id', $userId)->first();
        $patient = Patient::where('user_id', $userId)->first();
        $record = $doctor ?? $patient;
        $collection = $doctor ? Doctor::GOOGLE_JSON_FILE_PATH : Patient::GOOGLE_JSON_FILE_PATH;

        if (!$record) {
            return;
        }

        $media = $record->getFirstMedia($collection);

        if (!$media || !file_exists($media->getPath())) {
            return;
        }

        $json = json_decode(file_get_contents($media->getPath()), true);

        if (!isset($json['web']['client_id'], $json['web']['client_secret'])) {
            return;
        }

        $this->client = new Google_Client();
        $this->client->setApplicationName(config('app.name'));
        $this->client->setClientId($json['web']['client_id']);
        $this->client->setClientSecret($json['web']['client_secret']);
        $this->client->setRedirectUri(route('googleCalendarCallback'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->addScope(Google_Service_Calendar::CALENDAR);

        $integration = GoogleCalendarIntegration::whereUserId($userId)->first();

        if ($integration) {
            // Reconstruct token data properly
            $tokenData = [
                'access_token' => $integration->access_token,
                'expires_in' => 3599, // Default expiration time
            ];

            // Add refresh token from meta if available
            $meta = json_decode($integration->meta, true);
            if (!empty($meta['refresh_token'])) {
                $tokenData['refresh_token'] = $meta['refresh_token'];
            }

            $this->client->setAccessToken($tokenData);

            if ($this->client->isAccessTokenExpired()) {
                if (!empty($meta['refresh_token'])) {
                    $newToken = $this->client->fetchAccessTokenWithRefreshToken($meta['refresh_token']);

                    $integration->update([
                        'access_token' => $newToken['access_token'],
                        'meta' => json_encode($newToken),
                        'last_used_at' => now(),
                    ]);

                    $this->client->setAccessToken($newToken);
                }
            }
        }
    }

    public function oauth()
    {
        $this->initializeGoogleClient();

        if (!$this->client) {
            return redirect($this->getConnectGoogleCalendarRedirect())
                ->with('error', 'Invalid Google OAuth configuration');
        }

        return redirect()->away($this->client->createAuthUrl());
    }

    public function redirect(Request $request)
    {
        $this->initializeGoogleClient();

        if (!$this->client || !$request->has('code')) {
            return redirect($this->getConnectGoogleCalendarRedirect())
                ->with('error', 'Authorization failed');
        }

        $userId = session('google_oauth_user_id') ?? Auth::id();

        $token = $this->client->fetchAccessTokenWithAuthCode($request->get('code'));

        GoogleCalendarIntegration::updateOrCreate(
            ['user_id' => $userId],
            [
                'access_token' => $token['access_token'],
                'meta' => json_encode($token),
                'last_used_at' => now(),
            ]
        );

        $this->client->setAccessToken($token);

        GoogleCalendarList::whereUserId($userId)->delete();

        $service = new Google_Service_Calendar($this->client);
        $calendarList = $service->calendarList->listCalendarList();

        foreach ($calendarList->getItems() as $calendar) {
            if ($calendar->accessRole === 'owner') {
                GoogleCalendarList::create([
                    'user_id' => $userId,
                    'calendar_name' => $calendar->getSummary(),
                    'google_calendar_id' => $calendar->getId(),
                    'meta' => json_encode($calendar),
                ]);
            }
        }

        return redirect($this->getConnectGoogleCalendarRedirect($userId))
            ->with('success', 'Google Calendar connected successfully');
    }

    private function getConnectGoogleCalendarRedirect(?int $userId = null): string
    {
        $userId ??= session('google_oauth_user_id') ?? Auth::id();

        if ($userId && Patient::where('user_id', $userId)->exists()) {
            return '/patient/connect-google-calendar';
        }

        return '/doctors/connect-google-calendar';
    }

    public function syncCalendars()
    {
        $userId = Auth::id();

        $integration = GoogleCalendarIntegration::whereUserId($userId)->first();

        if (!$integration) {
            return [
                'success' => false,
                'message' => 'Google Calendar not connected',
            ];
        }

        $this->initializeGoogleClient();

        if (!$this->client) {
            return [
                'success' => false,
                'message' => 'Google client initialization failed',
            ];
        }

        try {
            $service = new Google_Service_Calendar($this->client);
            $calendarList = $service->calendarList->listCalendarList();

            $existing = GoogleCalendarList::whereUserId($userId)
                ->pluck('google_calendar_id')
                ->toArray();

            $syncedIds = [];

            foreach ($calendarList->getItems() as $calendar) {
                if ($calendar->accessRole === 'owner') {
                    GoogleCalendarList::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'google_calendar_id' => $calendar->getId(),
                        ],
                        [
                            'calendar_name' => $calendar->getSummary(),
                            'meta' => json_encode($calendar),
                        ]
                    );

                    $syncedIds[] = $calendar->getId();
                }
            }

            // Cleanup removed calendars
            $removed = array_diff($existing, $syncedIds);

            GoogleCalendarList::whereUserId($userId)
                ->whereIn('google_calendar_id', $removed)
                ->delete();

            AppointmentGoogleCalendar::whereUserId($userId)
                ->whereIn('google_calendar_id', $removed)
                ->delete();

            return [
                'success' => true,
                'message' => 'Google Calendar synced successfully',
            ];
        } catch (\Exception $e) {

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
