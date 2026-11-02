<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Models\Doctor;
use App\Models\Service;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServicesForm
{
    public static function configure(Schema $schema): Schema
    {
        $doctor = self::getDoctorOptions();

        return $schema
            ->components([
                Section::make([
                    TextInput::make('name')
                        ->label(__('messages.web.name').':')
                        ->placeholder(__('messages.web.name'))
                        ->required()
                        ->unique(Service::class, 'name', ignoreRecord: true),
                    Select::make('category_id')
                        ->label(__('messages.service.category').':')
                        ->required()
                        ->native(false)
                        ->relationship('serviceCategory', 'name')
                        ->placeholder(__('messages.service.category'))
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label(__('messages.web.name').':')
                                ->required()
                                ->unique('service_categories', 'name'),
                        ])
                        ->createOptionUsing(function (array $data) {
                            $category = \App\Models\ServiceCategory::create($data);
                            return $category->id;
                        })
                        ->createOptionAction(
                            fn ($action) => $action->modalWidth('md')
                        ),
                    TextInput::make('charges')
                        ->label(__('messages.service.charges').':')
                        ->numeric()
                        ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                        ->minValue(0)
                        ->placeholder(__('messages.service.charges'))
                        ->required(),
                    Select::make('doctor_id')
                        ->label(__('messages.doctors').':')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->relationship('serviceDoctors', 'id')
                        ->options($doctor)
                        ->getSearchResultsUsing(fn (string $search): array => self::getDoctorOptions(search: $search))
                        ->getOptionLabelsUsing(fn (array $values): array => self::getDoctorOptions(selectedDoctorIds: $values))
                        ->placeholder(__('messages.doctors'))
                        ->required(),
                    Textarea::make('short_description')
                        ->label(__('messages.service.short_description').':')
                        ->hintIcon('heroicon-o-question-mark-circle')
                        ->hintIconTooltip(__('Maximum 60 characters allowed'))
                        ->maxLength(60)
                        ->rows(5)
                        ->required()
                        ->placeholder(__('messages.service.short_description'))
                        ->columnSpanFull(),
                    SpatieMediaLibraryFileUpload::make('icon')
                        ->label(__('messages.front_service.icon').':')
                        ->image()
                        ->avatar()
                        ->collection('icon')
                        ->required(),
                    Toggle::make('status')
                        ->label(__('messages.web.status').':')
                        ->inline(false),
                ])->columns(2)->columnSpanFull(),
            ]);
    }

    protected static function getDoctorOptions(?string $search = null, ?array $selectedDoctorIds = null): array
    {
        if ($selectedDoctorIds === []) {
            return [];
        }

        return Doctor::query()
            ->join('users', 'doctors.user_id', '=', 'users.id')
            ->where('users.status', true)
            ->when(filled($search), function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('users.first_name', 'like', "%{$search}%")
                        ->orWhere('users.last_name', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(users.first_name, ' ', users.last_name) like ?", ["%{$search}%"]);
                });
            })
            ->when(filled($selectedDoctorIds), fn ($query) => $query->whereIn('doctors.id', $selectedDoctorIds))
            ->orderBy('users.first_name')
            ->orderBy('users.last_name')
            ->when(filled($search), fn ($query) => $query->limit(50))
            ->selectRaw('doctors.id as doctor_id, CONCAT(users.first_name, " ", users.last_name) as full_name')
            ->pluck('full_name', 'doctor_id')
            ->toArray();
    }
}
