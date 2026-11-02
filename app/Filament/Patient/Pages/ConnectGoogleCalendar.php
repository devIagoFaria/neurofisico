<?php

namespace App\Filament\Patient\Pages;

use App\Enums\NavigationSort;
use App\Models\AppointmentGoogleCalendar;
use App\Models\GoogleCalendarIntegration;
use App\Models\GoogleCalendarList;
use App\Models\Patient;
use BackedEnum;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Throwable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ConnectGoogleCalendar extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.patient.pages.connect-google-calendar';
    protected static string|BackedEnum|null $navigationIcon = Phosphor::CalendarStar;
    protected static ?int $navigationSort = NavigationSort::PATIENTS->value;

    public ?array $data = [];

    public $googleCalendarIntegrationExists = false;
    public $googleCalendarLists = [];
    public $checkTimeZone = null;
    public $doctorRole = false;
    public $patientRole = false;
    public $selectedCalendars = [];

    public static function canAccess(): bool
    {
        return false;
    }


    public static function getNavigationLabel(): string
    {
        return (__('messages.setting.connect_google_calendar'));
    }

    public function mount(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->doctorRole = $user->hasRole('doctor');
        $this->patientRole = $user->hasRole('patient');

        $this->googleCalendarIntegrationExists = GoogleCalendarIntegration::whereUserId($user->id)->exists();

        if ($this->googleCalendarIntegrationExists) {
            $this->googleCalendarLists = GoogleCalendarList::whereUserId($user->id)->get();
            $this->selectedCalendars = AppointmentGoogleCalendar::whereUserId($user->id)
                ->pluck('google_calendar_list_id')
                ->toArray();
        }

        $patient = Patient::where('user_id', $user->id)->first();
        if ($patient) {
            $this->form->fill([
                'json_file' => '',
            ]);
        }
    }

    public function form(Schema $form): Schema
    {
        $form->model = Patient::where('user_id', Auth::id())->first();

        return $form
            ->schema([
                Grid::make()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('json_file')
                            ->label(new HtmlString('<span class="text-black dark:text-white">'.e(__('messages.setting.google_json_file')).':</span>'))
                            ->collection(Patient::GOOGLE_JSON_FILE_PATH)
                            ->disk('google_json_file')
                            ->required()
                            ->acceptedFileTypes(['application/json'])
                            ->getUploadedFileUsing(static function (SpatieMediaLibraryFileUpload $component, string $file): ?array {
                                if (! $component->getRecord()) {
                                    return null;
                                }

                                $record = Patient::with('media')->where('user_id', $component->getRecord()->user_id)->first();
                                $media = $record?->getRelationValue('media')->firstWhere('uuid', $file);

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
                    ->statePath('data'),
            ]);
    }

    public function saveFilePath(): void
    {
        $this->form->getState();

        $patient = Patient::where('user_id', Auth::id())->first();
        $media = $patient?->getFirstMedia(Patient::GOOGLE_JSON_FILE_PATH);

        if ($media) {
            if (! file_exists($media->getPath())) {
                Notification::make()
                    ->title(__('messages.setting.file_processing_error'))
                    ->danger()
                    ->send();
                return;
            }

            Notification::make()
                ->title(__('messages.flash.json_file_upload'))
                ->success()
                ->send();

            $this->form->fill();
        } else {
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
        $patient = Patient::where('user_id', Auth::id())->first();

        if (! $patient || ! $patient->getFirstMedia(Patient::GOOGLE_JSON_FILE_PATH)) {
            Notification::make()
                ->title(__('messages.flash.please_upload_json_file_first'))
                ->danger()
                ->send();
            return;
        }

        session([
            'google_oauth_user_id' => Auth::id(),
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

                $this->redirect('/patient/connect-google-calendar');
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

    public function disconnectGoogleCalendar(): void
    {
        $user = Auth::user();
        AppointmentGoogleCalendar::whereUserId($user->id)->delete();
        GoogleCalendarIntegration::whereUserId($user->id)->delete();
        GoogleCalendarList::whereUserId($user->id)->delete();

        Notification::make()->title(__('messages.flash.google_calendar_disconnect'))->success()->send();
        $this->redirect('/patient/connect-google-calendar');
    }
}
