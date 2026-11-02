<?php

namespace App\Filament\Resources\Staff\Pages;

use App\Filament\Resources\Staff\StaffResource;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ViewStaff extends ViewRecord
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            backActionResource(static::$resource::getUrl()),
        ];
    }

    public function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(12)
                            ->schema([
                                SpatieMediaLibraryImageEntry::make('profile_image')
                                    ->collection(User::PROFILE)
                                    ->hiddenLabel()
                                    ->imageWidth(96)
                                    ->disk(config('app.media_disk'))
                                    ->imageHeight(96)
                                    ->circular()
                                    ->defaultImageUrl(fn ($record) => ! $record->hasMedia(User::PROFILE)
                                            ? getUserImageInitial($record->id, $record->full_name)
                                            : null
                                    )
                                    ->columnSpan(1),

                                Group::make([
                                    TextEntry::make('role_name')
                                        ->hiddenLabel()
                                        ->color('success'),

                                    TextEntry::make('full_name')
                                        ->hiddenLabel()
                                        ->weight('bold')
                                        ->size('lg')
                                        ->color('primary'),

                                    TextEntry::make('email')
                                        ->hiddenLabel()
                                        ->formatStateUsing(fn ($state) => "<a href='mailto:{$state}'>{$state}</a>"
                                        )
                                        ->html(),

                                    TextEntry::make('contact')
                                        ->hiddenLabel()
                                        ->state(function ($record) {

                                            if (! empty($record->contact)) {
                                                return '+'.$record->region_code.' '.$record->contact;
                                            }

                                            return __('messages.common.n/a');
                                        })
                                        ->default(__('messages.common.n/a')),
                                ])
                                    ->columnSpan(10),
                            ]),
                    ])
                    ->columnSpanFull(),
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make(__('messages.common.overview'))->schema([
                            Grid::make(12)
                                ->schema([
                                    TextEntry::make('role_name')
                                        ->label(__('messages.role.role'))
                                        ->columnSpan(6),
                                    TextEntry::make('permissions')
                                        ->label(__('messages.role.permissions'))
                                        ->state(function ($record) {
                                            if (! $record->getAllPermissions()->count()) {
                                                return __('messages.common.n/a');
                                            }

                                            return $record->getAllPermissions()->pluck('display_name');
                                        })
                                        ->badge()
                                        ->color(fn ($state) => getBadgeColor(crc32($state)))
                                        ->columnSpan(6),
                                    TextEntry::make('gender')
                                        ->label(__('messages.staff.gender'))
                                        ->state(function ($record) {
                                            return match ($record->gender) {
                                                User::MALE => __('messages.patient.male'),
                                                User::FEMALE => __('messages.patient.female'),
                                                default => __('messages.common.n/a'),
                                            };
                                        })
                                        ->default(__('messages.common.n/a'))
                                        ->columnSpan(6),
                                    TextEntry::make('created_at')
                                        ->label(__('messages.patient.registered_on'))
                                        ->since()
                                        ->columnSpan(6),
                                    TextEntry::make('updated_at')
                                        ->label(__('messages.patient.last_updated'))
                                        ->since()
                                        ->columnSpan(6),
                                ]),
                        ]),
                    ])->columnSpanFull(),
            ]);
    }
}
