<?php

namespace App\Filament\Doctor\Pages;

use BackedEnum;
use Filament\Pages\Page;
use App\Enums\NavigationSort;
use App\Models\Doctor;
use App\Models\GoogleCalendarIntegration;
use App\Models\GoogleCalendarList;
use App\Models\AppointmentGoogleCalendar;
use Google_Client;
use Google_Service_Calendar;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use ToneGabes\Filament\Icons\Enums\Phosphor;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Throwable;

class ConnectGoogleCalendar extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.doctor.pages.connect-google-calendar';
    protected static string|BackedEnum|null $navigationIcon = Phosphor::CalendarStar;
    protected static ?int $navigationSort = NavigationSort::SMART_PATIENT_CARDS->value;

    public static function canAccess(): bool
    {
        return false;
    }


    public function getTitle(): string
    {
        return (__('messages.setting.connect_google_calendar'));
    }

    /**
     * Data array to hold form state.
     */
    public ?array $data = [];

    public $googleCalendarIntegrationExists = false;
    public $googleCalendarLists = [];
    public $checkTimeZone = null;
    public $doctorRole = false;
    public $patientRole = false;
    public $selectedCalendars = [];

    public static function getNavigationLabel(): string
    {
        return (__('messages.setting.connect_google_calendar'));
    }

    public $jsonFileUploaded = false;
    public function mount(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->doctorRole = $user->hasRole('doctor');
        $this->patientRole = $user->hasRole('patient');

        if ($this->doctorRole) {
            $this->checkTimeZone = $user;
        }

        $this->googleCalendarIntegrationExists = GoogleCalendarIntegration::whereUserId($user->id)->exists();

        if ($this->googleCalendarIntegrationExists) {
            $this->googleCalendarLists = GoogleCalendarList::whereUserId($user->id)->get();
            $this->selectedCalendars = AppointmentGoogleCalendar::whereUserId($user->id)
                ->pluck('google_calendar_list_id')
                ->toArray();
        }

        // Fill form from existing Media Library data
        $doctor = Doctor::where('user_id', $user->id)->first();
        if ($doctor) {
            $this->jsonFileUploaded = $doctor->getFirstMedia(Doctor::GOOGLE_JSON_FILE_PATH) !== null;

            $this->form->fill([
                'json_file' => ''
            ]);
        }
    }

    public function form(Schema $form): Schema
    {
        $form->model = Doctor::where('user_id', Auth::id())->first();
        return $form
            ->schema([
                Grid::make()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('json_file')
                            ->label(new HtmlString('<span class="text-black dark:text-white">'.e(__('messages.setting.google_json_file')).':</span>'))
                            ->collection(Doctor::GOOGLE_JSON_FILE_PATH)
                            ->disk('google_json_file')
                            ->required()
                            ->acceptedFileTypes(['application/json'])
                            ->getUploadedFileUsing(static function (SpatieMediaLibraryFileUpload $component, string $file): ?array {
                                if (! $component->getRecord()) {
                                    return null;
                                }
                                $record = Doctor::with('media')->where('user_id', $component->getRecord()->user_id)->first();
                                $media = $record->getRelationValue('media')->firstWhere('uuid', $file);

                                $url = null;

                                if ($component->getVisibility() === 'private') {
                                    $conversion = $component->getConversion();

                                    try {
                                        $url = $media?->getTemporaryUrl(
                                            now()->addMinutes(5),
                                            (filled($conversion) && $media->hasGeneratedConversion($conversion)) ? $conversion : '',
                                        );
                                    } catch (Throwable $exception) {
                                        // This driver does not support creating temporary URLs.
                                    }
                                }

                                if ($component->getConversion() && $media?->hasGeneratedConversion($component->getConversion())) {
                                    $url ??= $media->getUrl($component->getConversion());
                                }

                                $url ??= $media?->getUrl();

                                return [
                                    'name' => $media?->getAttributeValue('name') ?? $media?->getAttributeValue('file_name'),
                                    'size' => $media?->getAttributeValue('size'),
                                    'type' => $media?->getAttributeValue('mime_type'),
                                    'url' => $url,
                                ];
                            }),
                    ])->columns(2)
                    ->statePath('data')
            ]);
    }

    public function saveFilePath(): void
    {
        $this->form->getState();

        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();

        $media = $doctor->getFirstMedia(Doctor::GOOGLE_JSON_FILE_PATH);

        if ($media) {
            if (!file_exists($media->getPath())) {
                Notification::make()
                    ->title(__('messages.setting.file_processing_error'))
                    ->danger()
                    ->send();
                return;
            }

            $this->jsonFileUploaded = true;

            Notification::make()
                ->title(__('messages.flash.json_file_upload'))
                ->success()
                ->send();

            $this->form->fill();
        } else {
            $this->jsonFileUploaded = false;
            Notification::make()
                ->title(__('messages.flash.please_upload_json_file_first'))
                ->danger()
                ->send();
        }
    }

    public function saveCalendars(): void
    {
        $user = Auth::user();
        AppointmentGoogleCalendar::whereUserId($user->id)->delete();

        foreach ($this->selectedCalendars as $calendarListId) {
            $calendarList = GoogleCalendarList::find($calendarListId);
            if ($calendarList) {
                AppointmentGoogleCalendar::create([
                    'user_id' => $user->id,
                    'google_calendar_list_id' => $calendarListId,
                    'google_calendar_id' => $calendarList->google_calendar_id,
                ]);
            }
        }

        Notification::make()->title(__('messages.flash.calender_added'))->success()->send();
    }

    public function connectGoogleCalendar(): void
    {
        $doctor = Doctor::where('user_id', Auth::id())->first();

        if (!$doctor || !$doctor->getFirstMedia(Doctor::GOOGLE_JSON_FILE_PATH)) {
            Notification::make()
                ->title(__('messages.flash.please_upload_json_file_first'))
                ->danger()
                ->send();
            return;
        }

        session([
            'google_oauth_user_id' => Auth::id()
        ]);

        $this->redirect(route('googleAuth'));
    }

    public function syncGoogleCalendar(): void
    {
        try {
            $controller = new \App\Http\Controllers\GoogleCalendarController();
            $result = $controller->syncCalendars();

            if ($result['success']) {
                Notification::make()
                    ->title($result['message'])
                    ->success()
                    ->send();

                $this->redirect('/doctors/connect-google-calendar');
            } else {
                Notification::make()
                    ->title($result['message'])
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Log::error('Google Calendar Sync Error: ' . $e->getMessage());
            Notification::make()
                ->title(__('messages.flash.google_calendar_sync_error') . ': ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function getAccessToken($user)
    {
        $integration = GoogleCalendarIntegration::whereUserId($user->id)->first();

        if (!$integration) {
            Notification::make()
                ->title(__('messages.flash.google_calendar_not_connected'))
                ->danger()
                ->send();
            return null;
        }

        $tokenData = json_decode($integration->access_token, true);

        if (isset($tokenData['access_token'])) {
            return $tokenData['access_token'];
        }

        return null;
    }

    public function disconnectGoogleCalendar(): void
    {
        $user = Auth::user();
        AppointmentGoogleCalendar::whereUserId($user->id)->delete();
        GoogleCalendarIntegration::whereUserId($user->id)->delete();
        GoogleCalendarList::whereUserId($user->id)->delete();

        Notification::make()->title(__('messages.flash.google_calendar_disconnect'))->success()->send();
        $this->redirect('/doctors/connect-google-calendar');
    }
}
