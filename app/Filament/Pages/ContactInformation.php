<?php

namespace App\Filament\Pages;

use App\Enums\NavigationSort;
use Filament\Pages\Page;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Models\Country;
use App\Models\Setting;
use App\Models\State;
use App\Models\City;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ContactInformation extends Page implements HasForms
{
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        return checkPermission('manage_settings');
    }

    protected string $view = 'filament.pages.contact-information';
    protected static ?string $cluster = SettingsCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::PhoneList;
    protected static ?int $navigationSort = NavigationSort::CONTACT_INFORMATION->value;

    public ?array $data = [];
    public ?array  $record = [];

    public static function getNavigationLabel(): string
    {
        return (__('messages.setting.contact_information'));
    }

    public function getTitle(): string
    {
        return __('messages.setting.contact_information');
    }

    public function mount(): void
    {
        $this->record = Setting::pluck('value', 'key')->toArray();

        $this->form->fill([
            'address_one' => $this->record['address_one'] ?? null,
            'address_two' => $this->record['address_two'] ?? null,
            'country_id' => $this->record['country_id'] ?? null,
            'state_id' => $this->record['state_id'] ?? null,
            'city_id' => $this->record['city_id'] ?? null,
            'postal_code' => $this->record['postal_code'] ?? null,
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make(__('messages.setting.contact_information'))
                    ->schema([
                        Grid::make(1)->schema([
                            TextInput::make('address_one')
                                ->label(__('messages.setting.address') . ' 1' . ':')
                                ->inlineLabel()
                                ->placeholder(__('messages.setting.address') . ' 1')
                                ->required(),
                            TextInput::make('address_two')
                                ->label(__('messages.setting.address') . ' 2' . ':')
                                ->inlineLabel()
                                ->placeholder(__('messages.setting.address') . ' 2'),
                            Select::make('country_id')
                                ->label(__('messages.country.country') . ':')
                                ->inlineLabel()
                                ->placeholder(__('messages.country.country'))
                                ->options(Country::toBase()->pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->native(false)
                                ->reactive()
                                ->afterStateUpdated(function (callable $set) {
                                    $set('state_id', null);
                                    $set('city_id', null);
                                }),
                            Select::make('state_id')
                                ->label(__('messages.state.state') . ':')
                                ->inlineLabel()
                                ->placeholder(__('messages.state.state'))
                                ->options(function (callable $get) {
                                    $countryId = $get('country_id');
                                    return $countryId ? State::where('country_id', $countryId)->pluck('name', 'id') : [];
                                })
                                ->native(false)
                                ->required()
                                ->reactive()
                                ->searchable(),
                            Select::make('city_id')
                                ->label(__('messages.city.city') . ':')
                                ->inlineLabel()
                                ->options(function (callable $get) {
                                    $stateId = $get('state_id');
                                    return $stateId ? City::where('state_id', $stateId)->pluck('name', 'id') : [];
                                })
                                ->native(false)
                                ->required()
                                ->reactive()
                                ->searchable(),
                            TextInput::make('postal_code')
                                ->label(__('messages.setting.postal_code') . ':')
                                ->inlineLabel()
                                ->placeholder(__('messages.setting.postal_code'))
                                ->required(),
                        ])
                    ])
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        try {
            foreach ($data as $key => $value) {
                $setting = Setting::where('key', $key)->first();
                if ($setting) {
                    $setting->update(['value' => $value]);
                } else {
                    Setting::create(['key' => $key, 'value' => $value]);
                }
            }

            Notification::make()
                ->success()
                ->title(__('messages.flash.setting_update'))
                ->send();
        } catch (\Exception $exception) {
            $this->validate([
                'data' => 'required|array',
            ], [
                'data.required' => $exception->getMessage(),
            ]);
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('messages.user.save_changes'))
                ->action('save'),
        ];
    }
}
