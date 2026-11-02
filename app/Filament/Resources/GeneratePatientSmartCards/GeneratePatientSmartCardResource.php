<?php

namespace App\Filament\Resources\GeneratePatientSmartCards;

use BackedEnum;
use App\Models\Patient;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Enums\NavigationSort;
use Filament\Resources\Resource;
use App\Models\SmartPatientCards;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use ToneGabes\Filament\Icons\Enums\Phosphor;
use App\Filament\Resources\Patients\PatientResource;
use App\Filament\Tables\Columns\CustomIdentityColumn;
use App\Filament\Clusters\SmartPatientCard\SmartPatientCardCluster;

class GeneratePatientSmartCardResource extends Resource
{
    protected static ?string $model = SmartPatientCards::class;

    public static function canViewAny(): bool
    {
        return false; //auth()->user()->hasAnyRole(['clinic_admin', 'doctor']);
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::VideoConference;

    protected static ?string $cluster = SmartPatientCardCluster::class;

    protected static ?int $navigationSort = NavigationSort::GENERATE_PATIENT_CARDS->value;

    public static function getModelLabel(): string
    {
        return __('messages.smart_patient_card.generate_patient_smart_cards');
    }

    public static function form(Schema $schema): Schema
    {
        // Get templates for select dropdown
        $templates = SmartPatientCards::pluck('template_name', 'id')->toArray();

        // Get patients for select dropdown
        $patients = Patient::where('template_id', null)->with('user')->get()->pluck('user.full_name', 'user.id');

        return $schema
            ->components([
                Select::make('template_id')
                    ->label(__('messages.smart_patient_card.template') . ':')
                    ->options($templates)
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->placeholder(__('messages.smart_patient_card.select_template')),

                Radio::make('status')
                    ->label(__('messages.smart_patient_card.type') . ':')
                    ->required()
                    ->default(1)
                    ->options([
                        1 => __('messages.smart_patient_card.for_all_patient'),
                        3 => __('messages.smart_patient_card.remaning_patients'),
                        2 => __('messages.smart_patient_card.only_one_patient'),
                    ])
                    ->live(),

                Select::make('patient_id')
                    ->label(__('messages.smart_patient_card.patient') . ':')
                    ->options($patients)
                    ->required()
                    ->searchable()
                    ->placeholder(__('messages.smart_patient_card.select_patient'))
                    ->visible(fn ($get) => $get('status') == 2), // Only visible when "Only one patient" is selected
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => Patient::query()
                ->whereNot('template_id')
                ->with(['user', 'smartPatientCard'])
                ->select('patients.*'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                CustomIdentityColumn::make('user.first_name')
                    ->avatar(fn ($record) => $record->user->profile_image)
                    ->html()
                    ->primary(fn ($record) => $record->user->full_name)
                    ->primaryUrl(fn ($record) => PatientResource::getUrl('view',['record' => $record->user->id]))
                    ->label(__('messages.appointment.patient'))
                    ->color('#2b7fff')
                    ->toggleable(false)
                    ->secondary('user.email')
                    ->sortable(['first_name'])
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->size('md'),

                TextColumn::make('patient_unique_id')
                    ->label(__('messages.patient.patient_unique_id'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('smartPatientCard.template_name')
                    ->label(__('messages.smart_patient_card.templat_name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label(__('messages.patient.registered_on'))
                    ->dateTime(formattedDate())
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_generate_smart_card'))
            ->deferFilters(false)
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->recordActions([
                Action::make('download_pdf')
                    ->label(__('messages.common.download'))
                    ->icon(Phosphor::Download)
                    ->iconButton()
                    ->url(fn ($record) => route('admin.smartCardPdf', $record->id))
                    ->openUrlInNewTab(),

                Action::make('view_smart_card')
                    ->label(__('messages.common.view'))
                    ->icon(Phosphor::Eye)
                    ->iconButton()
                    ->modalContent(fn ($record): View => view(
                        'filament.resources.generate-patient-smart-cards.view-smart-card',
                        ['record' => $record],
                    ))
                    ->modalHeading('')
                    ->modalWidth(Width::TwoExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalFooterActions([]),

                DeleteAction::make()
                    ->iconButton()
                    ->action(function ($record) {
                        $record->update([
                            'template_id' => null,
                            'qr_code' => null,
                        ]);
                    })
                    ->successNotificationTitle(
                        __('messages.smart_patient_card.patient_smart_card_deleted')
                    ),
            ])
            ->bulkActions([
                                //
                            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageGeneratePatientSmartCards::route('/'),
        ];
    }
}
