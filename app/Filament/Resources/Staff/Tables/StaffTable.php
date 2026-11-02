<?php

namespace App\Filament\Resources\Staff\Tables;

use App\Filament\Resources\Staff\StaffResource;
use App\Filament\Tables\Columns\CustomIdentityColumn;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class StaffTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->where('type', User::STAFF)->where('id', '!=', Auth::id()))
            ->defaultSort('id', 'desc')
            ->columns([
                CustomIdentityColumn::make('full_name')
                    ->avatar(fn ($record) => $record->profile_image)
                    ->primary('full_name')
                    ->label(__('messages.user.full_name'))
                    ->color('primary')
                    ->toggleable(false)
                    ->color('#2b7fff')
                    ->primaryUrl(fn ($record) => StaffResource::getUrl('view', ['record' => $record]))
                    ->secondary('email')
                    ->sortable(['first_name'])
                    ->searchable(['first_name','last_name','email'])
                    ->size('md'),
                TextColumn::make('role_name')
                    ->label(__('messages.staff.role')),
                ToggleColumn::make('email_verified_at')
                    ->label(__('messages.common.email_verified'))
                    ->sortable()
                    ->disabled(fn ($record) => $record->email_verified_at)
                    ->updateStateUsing(function ($record, $state) {
                        $state ? $record->email_verified_at = now() : ' ';
                        $record->save();
                        notify(__('messages.flash.verified_email'));
                    })
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->recordActions([
                Action::make('send_reset_password')
                    ->hiddenLabel()
                    ->tooltip(__('auth.reset_password.reset_pwd_btn'))
                    ->icon(Phosphor::Envelope)
                    ->iconSize('md')
                    ->color('info')
                    ->modal(false)
                    ->action(function ($record) {
                        $token = Password::createToken($record);

                        $record->notify(new \App\Notifications\ResetPasswordNotification($token));

                        Notification::make()
                            ->title(__('passwords.sent'))
                            ->success()
                            ->send();
                    }),
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton()
                    ->successNotificationTitle(__('messages.flash.staff_delete')),
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->emptyStateHeading(__('messages.custom_table_message.no_staff'))
            ->striped();
    }
}
