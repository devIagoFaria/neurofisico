<?php

namespace App\Filament\Patient\Pages;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Notification as ModelsNotification;
use App\Models\Patient;
use App\Models\Review;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Mokhosh\FilamentRating\Components\Rating;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class Reviews extends Page implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'filament.patient.pages.reviews';

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Star;

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return false;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public static function getNavigationLabel(): string
    {
        return (__('messages.reviews'));
    }

    public function getTitle(): string
    {
        return __('messages.reviews');
    }

    public function editReviewAction(): Action
    {
        return Action::make('editReview')
            ->label(__('messages.review.edit_review'))
            ->icon(Phosphor::NotePencil)
            ->modalHeading(__('messages.review.edit_review'))
            ->modalWidth('md')
            ->form($this->getReviewFormSchema())
            ->fillForm(function (array $arguments) {
                $id = $arguments['review_id'] ?? null;
                if (! $id) {
                    return [];
                }

                return Review::find($id)?->toArray() ?? [];
            })
            ->action(function (array $data, array $arguments) {
                $id = $arguments['review_id'] ?? null;

                if ($id && $review = Review::find($id)) {
                    $review->update($data);
                    Notification::make()
                        ->title(__('messages.flash.review_edit'))
                        ->success()
                        ->send();
                }
            });
    }

    public function createReviewAction(): Action
    {
        return Action::make('createReview')
            ->label(__('messages.review.add_review'))
            ->icon(Phosphor::NotePencil)
            ->modalHeading(__('messages.review.write_a_review'))
            ->modalWidth('md')
            ->form($this->getReviewFormSchema())
            ->action(function (array $data, array $arguments) {
                $doctorId = $arguments['doctor_id'] ?? null;

                if ($doctorId) {
                    $patient = Patient::whereUserId(getLogInUserId())->first();

                    Review::create([
                        'doctor_id' => $doctorId,
                        'patient_id' => $patient->id,
                        'rating' => $data['rating'],
                        'review' => $data['review'],
                    ]);

                    ModelsNotification::create([
                        'title' => getLogInUser()->full_name.' just added '.$data['rating'].' star review for you.',
                        'type' => ModelsNotification::REVIEW,
                        'user_id' => Doctor::whereId($doctorId)->first()->user_id,
                    ]);

                    Notification::make()
                        ->title(__('messages.flash.review_add'))
                        ->success()
                        ->send();
                }
            });
    }

    protected function getReviewFormSchema(): array
    {
        return [
            Textarea::make('review')
                ->label(__('messages.review.review') . ':')
                ->rows(4)
                ->required(),
            Rating::make('rating')
                ->hiddenLabel()
                ->color('warning')
                ->required(),
        ];
    }

    protected function getViewData(): array
    {
        $patient = Patient::whereUserId(auth()->id())->first();
        if (! $patient) {
            return ['doctors' => []];
        }

        $doctorIds = Appointment::wherePatientId($patient->id)
            ->whereStatus(Appointment::CHECK_OUT)
            ->pluck('doctor_id')
            ->unique();

        $doctors = Doctor::with(['user', 'specializations', 'reviews' => fn ($q) => $q->where('patient_id', $patient->id)])
            ->whereIn('id', $doctorIds)
            ->get();

        return compact('doctors');
    }
}
