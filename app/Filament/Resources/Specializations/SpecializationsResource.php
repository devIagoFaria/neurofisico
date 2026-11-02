<?php

namespace App\Filament\Resources\Specializations;

use BackedEnum;
use App\Models\Doctor;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Enums\NavigationSort;
use App\Models\Specialization;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Filament\Resources\Specializations\Pages\ManageSpecializations;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class SpecializationsResource extends Resource
{
    protected static ?string $model = Specialization::class;

    public static function canViewAny(): bool
    {
        //return checkPermission('manage_specialities');
        return false;
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::ShieldPlus;
    protected static ?int $navigationSort = NavigationSort::SPECIALIZATIONS->value;

    protected static ?string $recordTitleAttribute = 'Specializations';
    public static function getModelLabel(): string
    {
        return __('messages.doctor.specialization');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('messages.common.name') . ':')
                    ->placeholder(__('messages.common.name'))
                    ->unique(Specialization::class, 'name', ignoreRecord: true)
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.common.name'))
                    ->searchable(),
            ])
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->filters([
                //
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_specializations'))
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->modalWidth(Width::Medium)
                    ->successNotificationTitle(__('messages.flash.specialization_update')),
                // DeleteAction::make()
                //     ->iconButton()
                //     ->successNotificationTitle(__('messages.flash.specialization_delete')),

                DeleteAction::make()
                    ->iconButton()
                    ->action(function (Specialization $record) {
                        $used = Doctor::whereHas('specializations', function ($q) use ($record) {
                            $q->where('specializations.id', $record->id);
                        })->exists();

                        if ($used) {
                            return Notification::make()
                                ->title(__('messages.flash.specialization_used_some_where'))
                                ->danger()
                                ->send();
                        }

                        $record->delete();
                        return Notification::make()
                            ->title(__('messages.flash.specialization_delete'))
                            ->success()
                            ->send();
                    })
                    ->successNotificationTitle(null),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSpecializations::route('/'),
        ];
    }
}
