<?php

namespace App\Filament\Doctor\Resources\LiveConsultations\Pages;

use App\Filament\Doctor\Resources\LiveConsultations\LiveConsultationsResource;
use App\Filament\Doctor\Resources\LiveConsultations\Schemas\LiveConsultationsForm;
use App\Models\LiveConsultation;
use App\Models\UserZoomCredential;
use App\Repositories\LiveConsultationRepository;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ListLiveConsultations extends ListRecords
{
    protected static string $resource = LiveConsultationsResource::class;

    protected function getHeaderActions(): array
    {
        $userId = Auth::id();
        $existingCredential = UserZoomCredential::where('user_id', $userId)->first();

        return [
            ActionGroup::make([
                CreateAction::make()
                    ->visible(fn() => in_array('doctor', Auth::user()->roles->pluck('name')->toArray()))
                    ->modal()
                    ->modalWidth('3xl')
                    ->groupedIcon('')
                    ->modalHeading(__('messages.live_consultation.new_live_consultation'))
                    ->createAnother(false)
                    ->successNotificationTitle(null)
                    ->action(function (array $data) {
                        try {
                            DB::beginTransaction();

                            app(LiveConsultationRepository::class)->store($data);

                            DB::commit();

                            Notification::make()
                                ->title(__('messages.flash.live_consultation_save'))
                                ->success()
                                ->send();
                        } catch (Exception $e) {
                            DB::rollBack();

                            $responseData = json_decode($e->getMessage(), true);

                            if (isset($responseData['error'])) {
                                $errorCode = $responseData['error']['code'];

                                if ($errorCode == 401) {
                                    Notification::make()->danger()->title(__('messages.google_meet.disconnect_or_reconnect'))->send();
                                }
                            }

                            Notification::make()->danger()->title($e->getMessage())->send();
                        }
                    }),
                Action::make('add_credentials')
                    ->label(__('messages.live_consultation.add_credential'))
                    ->modalWidth('md')
                    ->visible(fn() => in_array('doctor', Auth::user()->roles->pluck('name')->toArray()))
                    ->schema([
                        TextInput::make('zoom_api_key')
                            ->label(__('messages.live_consultation.zoom_api_key'))
                            ->placeholder(__('messages.live_consultation.zoom_api_key'))
                            ->required()
                            ->default($existingCredential?->zoom_api_key ?? ''),
                        TextInput::make('zoom_api_secret')
                            ->label(__('messages.live_consultation.zoom_api_secret'))
                            ->placeholder(__('messages.live_consultation.zoom_api_secret'))
                            ->required()
                            ->default($existingCredential?->zoom_api_secret ?? '')
                            ->belowContent(new HtmlString(
                                ' <a href="https://developers.zoom.us/docs/integrations/create/" target="_blank" class="text-primary-600">
                                    ' . __('messages.live_consultation.generate_oauth') . '
                                </a>'
                            )),
                    ])
                    ->action(function (array $data) use ($userId) {
                        try {
                            app(LiveConsultationRepository::class)->createUserZoom($data);

                            Notification::make()
                                ->title(__('messages.flash.user_zoom_credential_saved'))
                                ->success()
                                ->send();
                        } catch (Exception $e) {
                            Notification::make()
                                ->title($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])->button()
                ->icon(Phosphor::CaretDown)
                ->iconPosition('after')
                ->extraAttributes([
                    'class' => 'mx-3',
                ]),

            Action::make('connect_with_zoom')
                ->label(__('messages.prescription.connect_with_zoom'))
                ->color('success')
                ->action(function () {
                    $userZoomCredential = UserZoomCredential::where('user_id', getLogInUserId())->first();
                    if ($userZoomCredential == null) {

                        return Notification::make()->danger()->title(__('messages.new_change.add_credential'))->send();
                    }
                    $clientID = $userZoomCredential->zoom_api_key;
                    $callbackURL = config('app.zoom_callback');
                    $url = "https://zoom.us/oauth/authorize?client_id=$clientID&response_type=code&redirect_uri=$callbackURL";

                    return redirect($url);
                })
                ->visible(fn() => isZoomTokenExpire() && isRole('doctor')),
        ];
    }

    public function changeStatus($status, LiveConsultation $liveConsultation)
    {
        try {
            $liveConsultation->update([
                'status' => $status,
            ]);

            return Notification::make()->success()->title(__('messages.flash.status_update'))->send();
        } catch (Exception $e) {
            return Notification::make()->danger()->title($e->getMessage())->send();
        }
    }
}
