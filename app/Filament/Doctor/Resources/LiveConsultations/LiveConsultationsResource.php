<?php

namespace App\Filament\Doctor\Resources\LiveConsultations;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Enums\NavigationSort;
use App\Models\LiveConsultation;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Doctor\Resources\LiveConsultations\Pages\EditLiveConsultations;
use App\Filament\Doctor\Resources\LiveConsultations\Pages\ListLiveConsultations;
use App\Filament\Doctor\Resources\LiveConsultations\Pages\CreateLiveConsultations;
use App\Filament\Doctor\Resources\LiveConsultations\Schemas\LiveConsultationsForm;
use App\Filament\Doctor\Resources\LiveConsultations\Tables\LiveConsultationsTable;
use App\Models\Patient;
use App\Models\Doctor;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class LiveConsultationsResource extends Resource
{
    protected static ?string $model = LiveConsultation::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::VideoCamera;
    protected static ?int $navigationSort = NavigationSort::DOCTORS_HOLIDAYS->value;

    protected static ?string $recordTitleAttribute = 'Live Consultations';

    public static function canViewAny(): bool
    {
        return false;
    }

    public static function getModelLabel(): string
    {
        return __('messages.live_consultations');
    }
    public static function form(Schema $schema): Schema
    {
        $patients = Patient::with('user')->get()->pluck('user.full_name', 'id')->toArray();
        $doctors = Doctor::with('user')->get()->pluck('user.full_name', 'id')->toArray();

        return $schema->components([
            TextInput::make('consultation_title')
                ->label(__('messages.live_consultation.consultation_title'))
                ->placeholder(__('messages.live_consultation.consultation_title'))
                ->required(),
            Grid::make(2)
                ->schema([
                    DateTimePicker::make('consultation_date')
                        ->label(__('messages.live_consultation.consultation_date') . ':')
                        ->placeholder(__('messages.live_consultation.select_consultation_date'))
                        ->native(false)
                        ->minDate(today())
                        ->required(),
                    TextInput::make('consultation_duration_minutes')
                        ->label(__('messages.live_consultation.consultation_duration_minutes') . ':')
                        ->placeholder(__('messages.live_consultation.consultation_minutes'))
                        ->required(),
                    Select::make('patient_id')
                        ->label(__('messages.web.patient_name') . ':')
                        ->placeholder(__('messages.live_consultation.select_patient_name'))
                        ->options($patients)
                        ->required()
                        ->searchable()
                        ->native(false),
                    Select::make('doctor_id')
                        ->label(__('messages.doctor_session.doctor') . ' ' . __('messages.web.name') . ':')
                        ->placeholder(__('messages.live_consultation.select_doctor_name'))
                        ->options($doctors)
                        ->required()
                        ->searchable()
                        ->native(false),
                    Radio::make('host_video')
                        ->label(__('messages.live_consultation.host_video') . ':')
                        ->options([
                            1 => __('messages.live_consultation.enable'),
                            0 => __('messages.live_consultation.disabled'),
                        ])
                        ->required()
                        ->inline()
                        ->default(0),
                    Radio::make('participant_video')
                        ->label(__('messages.live_consultation.client_video') . ':')
                        ->options([
                            1 => __('messages.live_consultation.enable'),
                            0 => __('messages.live_consultation.disabled'),
                        ])
                        ->required()
                        ->inline()
                        ->default(0),
                ]),
            Textarea::make('description')
                ->label(__('messages.appointment.description') . ':')
                ->placeholder(__('messages.appointment.description')),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return LiveConsultationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()
                ->schema([
                    TextEntry::make('consultation_title')
                        ->label(__('messages.live_consultation.consultation_title') . ':'),
                    TextEntry::make('consultation_date')
                        ->label(__('messages.live_consultation.consultation_date') . ':')
                        ->dateTime('g:i A, jS M Y'),
                    TextEntry::make('consultation_duration_minutes')
                        ->label(__('messages.live_consultation.consultation_duration_minutes') . ':'),
                    TextEntry::make('patient.user.full_name')
                        ->label(__('messages.web.patient_name') . ':'),
                    Textentry::make('doctor.user.full_name')
                        ->label(__('messages.doctor_session.doctor') . ' ' . __('messages.web.name') . ':'),
                    TextEntry::make('host_video')
                        ->label(__('messages.live_consultation.host_video') . ':')
                        ->formatStateUsing(fn($record) => $record->host_video ? __('messages.live_consultation.enable') : __('messages.live_consultation.disabled')),
                    TextEntry::make('participant_video')
                        ->label(__('messages.live_consultation.client_video') . ':')
                        ->formatStateUsing(fn($record) => $record->participant_video ? __('messages.live_consultation.enable') : __('messages.live_consultation.disabled')),
                    TextEntry::make('description')
                        ->label(__('messages.appointment.description') . ':')
                        ->default(__('messages.common.n/a'))
                ])->columns(2),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLiveConsultations::route('/'),
            // 'create' => CreateLiveConsultations::route('/create'),
        ];
    }
}
