<?php

namespace App\Filament\Pages;

use App\Enums\NavigationSort;
use App\Filament\Clusters\FrontCms\FrontCmsCluster;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class Cms extends Page implements HasForms
{
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        //return checkPermission('manage_front_cms');
        return false;
    }

    public ?array $data = [];

    protected string $view = 'filament.pages.cms';

    protected static string|BackedEnum|null $navigationIcon = Phosphor::CirclesFour;

    protected static ?string $cluster = FrontCmsCluster::class;

    protected static ?int $navigationSort = NavigationSort::CMS->value;

    public static function getNavigationLabel(): string
    {
        return strtoupper(__('messages.cms.cms'));
    }

    public static function getModelLabel(): string
    {
        return __('messages.front_cms');
    }

    public function getTitle(): string
    {
        return __('messages.front_cms');
    }

    public ?array $record = [];

    public function mount(): void
    {
        $this->record = Setting::pluck('value', 'key')->toArray();

        $this->form->fill([
            'about_image_1' => getFileUploadStatePath($this->record['about_image_1'] ?? null),
            'about_image_2' => getFileUploadStatePath($this->record['about_image_2'] ?? null),
            'about_image_3' => getFileUploadStatePath($this->record['about_image_3'] ?? null),
            'about_title' => $this->record['about_title'] ?? null,
            'about_experience' => $this->record['about_experience'] ?? null,
            'about_short_description' => $this->record['about_short_description'] ?? null,
            'terms_conditions' => $this->record['terms_conditions'] ?? null,
            'privacy_policy' => $this->record['privacy_policy'] ?? null,
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('')
                    ->schema([
                        Grid::make(3)->schema([
                            FileUpload::make('about_image_1')
                                ->label(__('messages.cms.about_image').' 1:')
                                ->image()
                                ->disk('public')
                                ->directory('cms/about_image_1')
                                ->helperText(__('messages.doctor.allowed_img'))
                                ->required()
                                ->avatar(),
                            FileUpload::make('about_image_2')
                                ->label(__('messages.cms.about_image').' 2:')
                                ->image()
                                ->disk('public')
                                ->directory('cms/about_image_2')
                                ->helperText(__('messages.doctor.allowed_img'))
                                ->required()
                                ->avatar(),
                            FileUpload::make('about_image_3')
                                ->label(__('messages.cms.about_image').' 3:')
                                ->image()
                                ->disk('public')
                                ->directory('cms/about_image_3')
                                ->helperText(__('messages.doctor.allowed_img'))
                                ->required()
                                ->avatar(),
                        ]),
                        TextInput::make('about_title')
                            ->label(__('messages.web.about_title').':')
                            ->required(),
                        TextInput::make('about_experience')
                            ->label(__('messages.web.about_experience').':')
                            ->numeric()
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                            ->required(),
                        Textarea::make('about_short_description')
                            ->label(__('messages.web.about_short_description').':')
                            ->rows(5)
                            ->required(),
                    ]),

                Section::make('')
                    ->schema([
                        RichEditor::make('terms_conditions')
                            ->label(__('messages.cms.terms_conditions').':')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('')
                    ->schema([
                        RichEditor::make('privacy_policy')
                            ->label(__('messages.cms.privacy_policy').':')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        try {
            $data['about_image_1'] = getPublicFileUrl($data['about_image_1'] ?? null);
            $data['about_image_2'] = getPublicFileUrl($data['about_image_2'] ?? null);
            $data['about_image_3'] = getPublicFileUrl($data['about_image_3'] ?? null);

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
                ->title(__('messages.flash.cms_update'))
                ->send();
        } catch (\Exception $exception) {
            $this->validate([
                'data' => 'required|array',
            ], [
                'data.required' => $exception->getMessage(),
            ]);
        }

        $this->js('window.location.reload()');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('messages.common.save'))
                ->action('save'),
        ];
    }
}
