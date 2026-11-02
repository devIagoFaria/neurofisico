<?php

namespace App\Filament\Resources\Transactions\Pages;

use Carbon\Carbon;
use App\Models\Appointment;
use App\Models\Transaction;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Grid;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\Transactions\TransactionsResource;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backActionResource(static::$resource::getUrl()),
        ];
    }

    public function infolist(Schema $infolist): Schema
    {
        return $infolist->components([
            Section::make()
                ->components([
                    Grid::make(2)
                        ->components([
                            TextEntry::make('appointment_id')
                                ->label(__('messages.appointment.appointment_unique_id') . ':')
                                ->badge()
                                ->color('warning'),
                            TextEntry::make('transaction_id')
                                ->label(__('messages.transaction.transaction_id') . ':'),
                            TextEntry::make('amount')
                                ->label(__('messages.doctor_appointment.amount') . ':')
                                ->formatStateUsing(fn ($record) => getCurrencyIcon($record->currency_id) . number_format($record->amount, 2)),
                            TextEntry::make('type')
                                ->label(__('messages.appointment.payment_method') . ':')
                                ->formatStateUsing(fn ($state) => match ($state) {
                                    Appointment::MANUALLY => __('messages.payment_method.manually'),
                                    Appointment::STRIPE => __('messages.payment_method.stripe'),
                                    Appointment::PAYSTACK => __('messages.payment_method.paystack'),
                                    Appointment::PAYPAL => __('messages.payment_method.paypal'),
                                    Appointment::RAZORPAY => __('messages.payment_method.razorpay'),
                                    Appointment::AUTHORIZE => __('messages.payment_method.authorize'),
                                    default => __('messages.common.n/a'),
                                }),
                            TextEntry::make('appointment.date')
                                ->label(__('messages.appointment.appointment_date') . ':')
                                ->formatStateUsing(function ($state, $record) {
                                    if (!$record->appointment) {
                                        return __('messages.common.n/a');
                                    }

                                    $appointment = $record->appointment;

                                    return Carbon::parse($appointment->date)->isoFormat('DD MMM YYYY')
                                        . ' '
                                        . $appointment->from_time . ' ' . $appointment->from_time_type
                                        . ' - '
                                        . $appointment->to_time . ' ' . $appointment->to_time_type;
                                })
                                ->badge(),
                            TextEntry::make('appointment.patient.user.full_name')
                                ->label(__('messages.appointment.patient') . ':'),
                            TextEntry::make('appointment.doctor.user.full_name')
                                ->label(__('messages.doctor_session.doctor') . ':'),
                            TextEntry::make('status')
                                ->label(__('messages.web.status') . ':')
                                ->formatStateUsing(
                                    fn($state) =>
                                    Transaction::PAYMENT_STATUS[$state] ?? __('messages.common.n/a')
                                )
                                ->badge(),
                            TextEntry::make('acceptedPaymentUser.full_name')
                                ->label(__('messages.transaction.payment_accepted_by') . ':')
                                ->default('N/A'),
                        ]),
                ])->columnSpanFull(),
        ]);
    }
}
