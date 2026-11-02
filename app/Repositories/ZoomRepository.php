<?php

namespace App\Repositories;

use App\Models\LiveConsultation;
use App\Models\UserZoomCredential;
use App\Models\ZoomOAuth;
use Filament\Notifications\Notification;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class ZoomRepository
 */
class ZoomRepository
{
    // ... other methods ...
    public function connectWithZoom($code)
    {
        $userZoomCredential = UserZoomCredential::where('user_id', getLogInUserId())->first();
        if (isset($userZoomCredential)) {
            $clientID = $userZoomCredential->zoom_api_key;
            $secret = $userZoomCredential->zoom_api_secret;
        } else {
            return Notification::make()
                ->title(__('messages.new_change.add_credential'))
                ->success()
                ->send();
        }

        $client = new Client(['base_uri' => 'https://zoom.us']);
        try {
            $response = $client->request('POST', '/oauth/token', [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($clientID . ':' . $secret),
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => config('app.zoom_callback'),
                ],
            ]);

            $token = json_decode($response->getBody()->getContents(), true);

            // Check if the response contains an error
            if (isset($token['error'])) {
                // Check if the error is due to invalid credentials
                if ($token['error'] === 'invalid_client' || $token['error'] === 'invalid_grant' || strpos(strtolower($token['error_description'] ?? ''), 'invalid') !== false) {
                    Notification::make()
                        ->title(__('messages.new_change.invalid_zoom_credentials'))
                        ->danger()
                        ->send();
                    return false;
                }

                throw new \Exception('Zoom API Error: ' . $token['error'] . ' - ' . ($token['error_description'] ?? 'Unknown error'));
            }

            // Check if access_token and refresh_token exist in the response
            if (!isset($token['access_token']) || !isset($token['refresh_token'])) {
                throw new \Exception('Zoom API did not return required tokens');
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($responseBody, true);

            // Check if it's an invalid client error or invalid_grant error
            if (isset($errorData['error']) && ($errorData['error'] === 'invalid_client' || $errorData['error'] === 'invalid_grant')) {
                Notification::make()
                    ->title(__('messages.new_change.invalid_zoom_credentials'))
                    ->danger()
                    ->send();
                return false;
            }

            throw $e;
        }

        $exist = ZoomOAuth::where('user_id', Auth::id())->first();
        if (! $exist) {
            ZoomOAuth::create([
                'user_id' => Auth::id(),
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'],
            ]);
        } else {
            $exist->update([
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'],
            ]);
        }

        return true;
    }

    public function createZoomMeeting($data)
    {
        $client = new Client(['base_uri' => 'https://api.zoom.us']);
        $zoomOAuth = ZoomOAuth::where('user_id', Auth::id())->first();

        try {
            $response = $client->request('POST', '/v2/users/me/meetings', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $zoomOAuth->access_token,
                ],
                'json' => [
                    'topic' => $data['consultation_title'],
                    'type' => 2,
                    'start_time' => $this->toZoomTimeFormat($data['consultation_date']),
                    'duration' => $data['consultation_duration_minutes'],
                    'agenda' => (!empty($data['description'])) ? $data['description'] : null,
                    'password' => '123456',
                    'settings' => [
                        'host_video' => ($data['host_video'] == LiveConsultation::HOST_ENABLE) ? true : false,
                        'participant_video' => ($data['participant_video'] == LiveConsultation::CLIENT_ENABLE) ? true : false,
                        'waiting_room' => true,
                    ],
                ],
            ]);
            $data = json_decode($response->getBody());

            return (array) $data;
        } catch (\Exception $e) {
            if ($e->getCode() == 401) {
                if (! isset($clientID)) {
                    throw new UnprocessableEntityHttpException(__('messages.live_consultation.already_in_use'));
                }
                $userZoomCredential = UserZoomCredential::where('user_id', getLogInUserId())->first();
                if (!isset($userZoomCredential)) {

                    throw new UnprocessableEntityHttpException(__('messages.new_change.add_credential_zoom'));
                }
                $clientID = $userZoomCredential->zoom_api_key;
                $secret = $userZoomCredential->zoom_api_secret;
                $refresh_token = $zoomOAuth->refresh_token;
                $client = new Client(['base_uri' => 'https://zoom.us']);

                $response = $client->request('POST', '/oauth/token', [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($clientID . ':' . $secret),
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                    'form_params' => [
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $refresh_token,
                    ],
                ]);

                $refreshResponse = json_decode($response->getBody(), true);

                $zoomOAuth->update([
                    'access_token' => $refreshResponse['access_token'],
                    'refresh_token' => $refreshResponse['refresh_token'],
                ]);

                $this->createZoomMeeting($data);
            } else {
                if ($e->getCode() == 0) {
                    throw new UnprocessableEntityHttpException(__('messages.live_consultation.have_to_connect_zoom'));
                }
                throw new UnprocessableEntityHttpException($e->getMessage());
            }
        }
    }

    private function toZoomTimeFormat(string $dateTime)
    {
        try {
            $date = new \DateTime($dateTime);

            return $date->format('Y-m-d\TH:i:s');
        } catch (\Exception $e) {
            \Log::error('ZoomJWT->toZoomTimeFormat : ' . $e->getMessage());

            return '';
        }
    }

    public function zoomGet($id)
    {
        try {
            $liveCunsultation = LiveConsultation::whereMeetingId($id)->first();
            $client = new Client(['base_uri' => 'https://api.zoom.us']);
            if (isset($liveCunsultation)) {
                $zoomOAuth = ZoomOAuth::where('user_id', $liveCunsultation->created_by)->first();
            }
            $response = $client->request('GET', '/v2/meetings/' . $id, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $zoomOAuth->access_token,
                ],
            ]);

            $data = json_decode($response->getBody());

            return $data;
        } catch (\Exception $e) {
            if ($e->getCode() == 401 || $e->getCode() == 0) {
                throw new UnprocessableEntityHttpException(__('messages.live_consultation.have_to_connect_zoom'));
            }
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }
}
