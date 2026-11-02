<?php

namespace App\Filament\Resources\MedicineBills\Pages;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Livewire\MedicineDetail;
use Filament\Actions\EditAction;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Components\Grid;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use App\Filament\Resources\MedicineBills\MedicineBillsResource;
use App\Models\User;

class ViewMedicineBill extends ViewRecord
{
    protected static string $resource = MedicineBillsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->color('success')
                ->label(__('messages.medicine_bills.print_bill'))
                ->url(function ($record) {
                    return route('medicine.bill.pdf', $record->id);
                })
                ->openUrlInNewTab(),
            EditAction::make(),
            backActionResource(static::$resource::getUrl()),
        ];
    }

    public function infolist(Schema $infolist): Schema
    {
        return $infolist->components([
            Section::make()
                ->components([
                    Grid::make(12)
                        ->components([
                            ImageEntry::make('app_logo')
                                ->hiddenLabel()
                                ->state(null)
                                ->defaultImageUrl(getAppLogo())
                                ->columnSpan(9)
                                ->imageHeight(100)
                                ->imagewidth(100),

                            TextEntry::make('bill_number')
                                ->hiddenLabel()
                                ->prefix('#')
                                ->extraAttributes(['class' => 'text-3xl font-semibold'])
                                ->columnSpan(5),
                        ]),

                    Grid::make(4)
                        ->components([
                            TextEntry::make('patient.user.full_name')
                                ->label(__('messages.appointment.patient') . ':')
                                ->state(fn ($record) => $record->patient?->patientUser?->full_name ?? __('messages.common.n/a')),

                            TextEntry::make('bill_date')
                                ->label(__('messages.medicine_bills.bill_date') . ':')
                                ->formatStateUsing(
                                    fn($state) =>
                                    Carbon::parse($state)->format('jS M, Y g:i A')
                                ),

                            TextEntry::make('patient.user.email')
                                ->label(__('messages.medicine_bills.patient_email') . ':')
                                ->state(fn ($record) => $record->patient?->patientUser?->email ?? '-'),

                            TextEntry::make('payment_status')
                                ->label(__('messages.medicine_bills.payment_status') . ':')
                                ->formatStateUsing(
                                    fn($state) =>
                                    $state === 1
                                        ? __('messages.transaction.paid')
                                        : __('messages.transaction.pending')
                                ),

                            TextEntry::make('patient.patientUser.phone')
                                ->label(__('messages.medicine_bills.patient_cell_no') . ':')
                                ->default(__('messages.common.n/a')),

                            TextEntry::make('patient.patientUser.gender')
                                ->label(__('messages.medicine_bills.patient_gender') . ':')
                                ->state(function ($record) {
                            
                                    $gender = $record->patient?->patientUser?->gender;
                            
                                    return $gender
                                        ? User::GENDER[$gender] ?? __('messages.common.n/a')
                                        : __('messages.common.n/a');
                                }),

                            TextEntry::make('patient.patientUser.dob')
                                ->label(__('messages.medicine_bills.patient_dob') . ':')
                                ->getStateUsing(
                                    fn ($record) =>
                                    $record->patient?->patientUser?->dob
                                        ? Carbon::parse($record->patient?->patientUser?->dob)->translatedFormat('jS M, Y')
                                        : __('messages.common.n/a')
                                )                        ]),
                    Grid::make(4)
                        ->components([
                            TextEntry::make('created_at')
                                ->label(__('messages.doctor.created_at') . ':')
                                ->since(),

                            TextEntry::make('updated_at')
                                ->label(__('messages.patient.last_updated') . ':')
                                ->since(),
                        ]),
                    Grid::make(12)
                        ->components([
                            TextEntry::make('note')
                                ->hiddenLabel()
                                ->columnSpan(9),
                            Grid::make(3)
                                ->components([
                                    TextEntry::make('total')
                                        ->label(__('messages.purchase_medicine.total') . ':')
                                        ->numeric(decimalPlaces: 2)
                                        ->inlineLabel()
                                        ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                                        ->columnSpan(3),
                                    TextEntry::make('tax_amount')
                                        ->label(__('messages.purchase_medicine.tax') . ':')
                                        ->numeric(decimalPlaces: 2)
                                        ->inlineLabel()
                                        ->default('0')
                                        ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                                        ->columnSpan(3),
                                    TextEntry::make('discount')
                                        ->label(__('messages.purchase_medicine.discount') . ':')
                                        ->numeric(decimalPlaces: 2)
                                        ->inlineLabel()
                                        ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                                        ->columnSpan(3),
                                    TextEntry::make('net_amount')
                                        ->label(__('messages.purchase_medicine.net_amount') . ':')
                                        ->numeric(decimalPlaces: 2)
                                        ->inlineLabel()
                                        ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                                        ->columnSpan(3),
                                ])
                                ->columnSpan(3),
                        ])
                ])
                ->columnSpanFull(),
        ]);
    }

    protected function getFooterWidgets(): array
    {
        return [
            MedicineDetail::class,
        ];
    }
}
