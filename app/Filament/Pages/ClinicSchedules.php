<?php

namespace App\Filament\Pages;

use App\Enums\NavigationSort;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Models\ClinicSchedule;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ClinicSchedules extends Page implements HasForms
{
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        return checkPermission('manage_settings');
    }

    protected string $view = 'filament.pages.clinic-schedules';
    protected static ?string $cluster = SettingsCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::CalendarDots;
    protected static ?int $navigationSort = NavigationSort::CLINIC_SCHEDULES->value;

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return (__('messages.clinic_schedules'));
    }

    public function getTitle(): string
    {
        return __('messages.clinic_schedules');
    }

    public function mount(): void
    {
        $this->form->fill($this->loadState());
    }

    protected function loadState(): array
    {
        $state = [];
        $slots = getSchedulesTimingSlot();

        foreach (ClinicSchedule::WEEKDAY as $day => $label) {
            $schedule = ClinicSchedule::where('day_of_week', $day)->first();

            $state['days'][$day] = [
                'enabled' => $schedule !== null,
                'start_time' => $schedule?->start_time ?? array_key_first($slots),
                'end_time' => $schedule?->end_time ?? array_key_last($slots),
            ];
        }

        return $state;
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make()
                    ->schema($this->weekDayFields())
                    ->columns(1),
            ])
            ->statePath('data')
            ->model(ClinicSchedule::class);
    }

    protected function weekDayFields(): array
    {
        $fields = [];
        $slots = getSchedulesTimingSlot();

        foreach (ClinicSchedule::WEEKDAY as $day => $shortName) {
            $fields[] = Section::make()
                ->columns(4)
                ->schema([
                    Toggle::make("days.$day.enabled")
                        ->label(__('messages.short.'.$shortName))
                        ->reactive()
                        ->columnSpan(2),

                    Select::make("days.$day.start_time")
                        ->label(__('messages.common.start_time'))
                        ->hiddenLabel()
                        ->options($slots)
                        ->searchable()
                        ->disabled(fn($get) => ! $get("days.$day.enabled"))
                        ->required(fn($get) => $get("days.$day.enabled"))
                        ->columnSpan(1),

                    Select::make("days.$day.end_time")
                        ->label(__('messages.common.end_time'))
                        ->hiddenLabel()
                        ->options($slots)
                        ->searchable()
                        ->disabled(fn($get) => ! $get("days.$day.enabled"))
                        ->required(fn($get) => $get("days.$day.enabled"))
                        ->columnSpan(1)
                ])
                ->columnSpanFull();
        }

        return $fields;
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('messages.common.save'))
                ->action('save'),
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            $days = $data['days'] ?? [];

            $existingDays = ClinicSchedule::pluck('day_of_week')->toArray();
            $checkedDays = [];

            foreach ($days as $day => $config) {
                if (! $config['enabled']) {
                    continue;
                }

                $checkedDays[] = $day;

                // Validate time format and ensure start time is not after end time
                $startTime = strtotime($config['start_time']);
                $endTime = strtotime($config['end_time']);

                if ($startTime >= $endTime) {
                    Notification::make()
                        ->danger()
                        ->title(ClinicSchedule::WEEKDAY[$day] .
                            __('messages.start_time_invalid'))
                        ->send();
                    return;
                }

                ClinicSchedule::updateOrCreate(
                    ['day_of_week' => $day],
                    [
                        'start_time' => $config['start_time'],
                        'end_time' => $config['end_time'],
                    ]
                );
            }

            // Delete unchecked days
            foreach (array_diff($existingDays, $checkedDays) as $removeDay) {
                ClinicSchedule::where('day_of_week', $removeDay)->delete();
            }

            Notification::make()
                ->success()
                ->title(__('messages.flash.clinic_save'))
                ->send();

            // Refresh the form state after saving
            $this->form->fill($this->loadState());
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title(__('messages.error'))
                ->body($e->getMessage())
                ->send();
        }
    }
}
