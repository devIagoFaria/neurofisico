<?php

namespace App\Filament\Resources\MedicineBills\Tables;

use Carbon\Carbon;
use App\Models\User;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\Doctors\DoctorResource;
use App\Filament\Resources\Patients\PatientResource;
use App\Filament\Tables\Columns\CustomIdentityColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class MedicineBillsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('bill_number')
                    ->label(__('messages.medicine_bills.bill_number'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->prefix('#'),
                TextColumn::make('bill_date')
                    ->label(__('messages.appointment.date'))
                    ->searchable()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(
                        fn($state) => Carbon::parse($state)->format('g:i A') . '<br>' . Carbon::parse($state)->format('jS M, Y')
                    )
                    ->html(),
                CustomIdentityColumn::make('patient.patientUser.full_name')
                    ->label(__('messages.appointment.patient'))
                    ->primary(fn($record) => $record->patient?->patientUser?->full_name ?? __('messages.common.n/a'))
                    ->secondary(fn($record) => $record->patient?->patientUser?->email)
                    ->avatar(fn($record) => $record->patient?->patientUser?->profile_image)
                    ->color('#2b7fff')
                    ->toggleable(false)
                    ->primaryUrl(
                        fn($record) =>
                        $record->patient
                            ? PatientResource::getUrl('view', ['record' => $record->patient->user_id])
                            : null
                    )
                    ->default(__('messages.common.n/a'))
                    ->sortable(query: function ($query, string $direction) {
                        return $query
                            ->leftJoin('patients', 'medicine_bills.patient_id', '=', 'patients.id')
                            ->leftJoin('users as patient_users', 'patients.user_id', '=', 'patient_users.id')
                            ->orderBy('patient_users.first_name', $direction)
                            ->select('medicine_bills.*');
                    })
                    ->searchable(
                        query: function ($query, string $search) {
                            return $query->whereHas('patient.patientUser', function ($q) use ($search) {
                                $q->where('first_name', 'like', "%{$search}%")
                                  ->orWhere('last_name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                            });
                        }
                    )
                    ->size('md'),

                CustomIdentityColumn::make('doctor.doctorUser.full_name')
                    ->label(__('messages.doctor.doctor'))
                    ->primary(fn($record) => $record->doctor?->doctorUser?->full_name ?? __('messages.common.n/a'))
                    ->secondary(fn($record) => $record->doctor?->doctorUser?->email)
                    ->avatar(fn($record) => $record->doctor?->doctorUser?->profile_image)
                    ->color('#2b7fff')
                    ->toggleable(false)
                    ->primaryUrl(
                        fn($record) =>
                        $record->doctor
                            ? DoctorResource::getUrl('view', ['record' => $record->doctor->user_id])
                            : null
                    )
                    ->default(__('messages.common.n/a'))
                    ->sortable(query: function ($query, string $direction) {
                        return $query
                            ->leftJoin('doctors', 'medicine_bills.doctor_id', '=', 'doctors.id')
                            ->leftJoin('users as doctor_users', 'doctors.user_id', '=', 'doctor_users.id')
                            ->orderBy('doctor_users.first_name', $direction)
                            ->select('medicine_bills.*');
                    })
                    ->searchable(
                        query: function ($query, string $search) {
                            return $query->whereHas('doctor.doctorUser', function ($q) use ($search) {
                                $q->where('first_name', 'like', "%{$search}%")
                                  ->orWhere('last_name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                            });
                        }
                    )
                    ->size('md'),

                TextColumn::make('discount')
                    ->label(__('messages.purchase_medicine.discount'))
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('net_amount')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn($record) => getCurrencyIcon($record->currency_id) . number_format($record->net_amount, 2))
                    ->alignCenter()
                    ->label(__('messages.purchase_medicine.net_amount')),
                TextColumn::make('payment_status')
                    ->label(__('messages.appointment.payment') . ' ' . __('messages.appointment.status'))
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        return $state === 1 ? __('messages.transaction.paid') : __('messages.transaction.pending');
                    })
                    ->color(function ($state) {
                        return $state === 1 ? 'success' : 'danger';
                    })
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
            ])
            ->recordUrl(null)
            ->filters([
                SelectFilter::make('payment_status')
                    ->options([
                        '1' => __('messages.transaction.paid'),
                        '0' => __('messages.transaction.pending'),
                    ])
                    ->native(false)
                    ->label(__('messages.appointment.payment') . ' ' . __('messages.appointment.status')),
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_medicine_bills'))
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->color('success'),
                EditAction::make()
                    ->iconButton(),
                DeleteAction::make()
                    ->iconButton()
                    ->before(function ($record) {
                        $record->saleMedicine()->delete();
                    })
                    ->successNotificationTitle(__('messages.flash.medicine_bill_delete')),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
