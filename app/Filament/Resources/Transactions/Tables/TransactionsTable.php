<?php

namespace App\Filament\Resources\Transactions\Tables;

use Dom\Text;
use Filament\Tables\Table;
use App\Models\Appointment;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Toggle;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Tables\Columns\CustomIdentityColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Carbon;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();

                if ($user->hasRole('doctor')) {
                    $query->whereHas('appointment', function (Builder $q) use ($user) {
                        $q->where('doctor_id', $user->doctor->id);
                    });
                }

                if ($user->hasRole('patient'))
                    $query->where('user_id', $user->id);
            })
            ->defaultSort('id', 'desc')
            ->columns([
                CustomIdentityColumn::make('user.full_name')
                    ->label(__('messages.appointment.patient'))
                    ->avatar(fn($record) => $record->user->profile_image)
                    ->html()
                    ->primary(fn($record) => $record->user->full_name)
                    ->primaryUrl(function ($record) {
                        $user = Auth::user();

                        if ($user->hasRole('clinic_admin')) {
                            return route(
                                'filament.admin.resources.patients.view',
                                ['record' => $record->user->id]
                            );
                        }

                        if ($user->hasRole('doctor')) {
                            return route(
                                'filament.doctor.resources.patients.view',
                                ['record' => $record->user->patient->id]
                            );
                        }

                        return null;
                    })
                    ->color('#2b7fff')
                    ->toggleable(false)
                    ->secondary('user.email')
                    ->sortable(['first_name'])
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->size('md')
                    ->hidden(
                        fn($record) => Auth::user()->hasRole('patient')
                    ),

                TextColumn::make('created_at')
                    ->label(__('messages.appointment.date'))
                    ->badge()
                    ->sortable()
                    ->date('jS M, Y g:i A'),
                TextColumn::make('type')
                    ->label(__('messages.appointment.payment_method'))
                    ->sortable()
                    ->getStateUsing(fn ($record) => match ($record->type) {
                        Appointment::MANUALLY => __('messages.payment_method.manually'),
                        Appointment::STRIPE => __('messages.payment_method.stripe'),
                        Appointment::PAYSTACK => __('messages.payment_method.paystack'),
                        Appointment::PAYPAL => __('messages.payment_method.paypal'),
                        Appointment::RAZORPAY => __('messages.payment_method.razorpay'),
                        Appointment::AUTHORIZE => __('messages.payment_method.authorize'),
                        default => __('messages.common.n/a'),
                    }),
                TextColumn::make('appointment.status')
                    ->label(__('messages.appointment.appointment_status'))
                    ->sortable()
                    ->badge()
                    ->state(function ($record) {
                        $status = $record->appointment?->status;

                        return match ($status) {
                            1 => __('messages.common.booked'),
                            2 => __('messages.common.check in'),
                            3 => __('messages.common.check out'),
                            4 => __('messages.common.cancelled'),
                            default => __('messages.common.n/a'),
                        };
                    })
                    ->color(function ($record) {
                        $status = $record->appointment?->status;

                        return match ($status) {
                            1 => 'primary',
                            2 => 'success',
                            3 => 'warning',
                            4 => 'danger',
                            default => null,
                        };
                    })
                    ->hidden(fn($record) => Auth::user()->hasRole('patient')),
                TextColumn::make('amount')
                    ->label(__('messages.doctor_appointment.amount'))
                    ->formatStateUsing(fn($record) => getCurrencyIcon($record->currency_id) . number_format($record->amount, 2))
                    ->sortable(),
            ])
            ->filters([
                DateRangeFilter::make('created_at')
                    ->label(__('messages.web.created_at'))
                    ->placeholder(__('messages.common.pick_date_range'))
                    ->startDate(Carbon::now()->startOfWeek()->subDay())
                    ->endDate(Carbon::now()->endOfWeek()->subDay()),

                SelectFilter::make('status')
                    ->label(__('messages.web.status'))
                    ->native(false)
                    ->options([
                        Transaction::SUCCESS => __('messages.transaction.success'),
                        Transaction::PENDING => __('messages.transaction.pending'),
                    ]),
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_transaction'))
            ->deferFilters(false)
            ->recordActionsColumnLabel(__('messages.common.action'))
            ->recordActions([
                ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->color('primary')
                    ->visible(function ($record) {
                        $user = Auth::user();

                        $appointmentStatus = $record->appointment?->status;

                        if (
                            $appointmentStatus === Appointment::CANCELLED &&
                            $record->status === Transaction::PENDING
                        ) {
                            return true;
                        }

                        if ($record->status === Transaction::PENDING) {
                            return false;
                        }
                        return true;
                    }),
                Action::make('pending_badge')
                    ->label(__('messages.transaction.pending'))
                    ->badge()
                    ->color('warning')
                    ->visible(function ($record) {
                        $user = Auth::user();

                        return $user->hasRole('patient') && $record->status === Transaction::PENDING;
                    })
                    ->disabled(),

                Action::make('ApprovePayment')
                    ->icon('heroicon-o-check-circle')
                    ->iconButton()
                    ->color('success')
                    ->visible(
                        fn($record) =>
                        $record->status === Transaction::PENDING
                            && optional($record->appointment)->status !== Appointment::CANCELLED
                    )
                    ->action(
                        fn($record) =>
                        $record->update([
                            'status' => Transaction::SUCCESS,
                        ])
                    )
                    ->successNotificationTitle(__('messages.flash.payment_status_updated'))
                    ->hidden(fn() => Auth::user()->hasRole('patient')),
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
