<?php

namespace App\Livewire;

use Carbon\Carbon;
use Filament\Tables\Table;
use App\Models\PurchasedMedicine;
use Filament\Widgets\TableWidget;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class PurchaseMedicineDetail extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public $record;
    
    protected static ?string $heading = '';

    public function mount($record)
    {
        $this->record = $record->id;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => PurchasedMedicine::query()->where('purchase_medicines_id', $this->record))
            ->columns([
                TextColumn::make('medicines.name')
                    ->label(__('messages.medicine.medicines')),

                TextColumn::make('expiry_date')
                    ->label(__('messages.purchase_medicine.expiry_date'))
                    ->getStateUsing(fn ($record) => $record->expiry_date ? Carbon::parse($record->expiry_date)->translatedFormat('jS M, Y') : __('messages.common.n/a')),
                TextColumn::make('lot_no')
                    ->label(__('messages.purchase_medicine.lot_no'))
                    ->aligncenter(),

                TextColumn::make('medicines.buying_price')
                    ->label(__('messages.medicine.buying_price'))
                    ->aligncenter(),

                TextColumn::make('medicines.selling_price')
                    ->label(__('messages.medicine.selling_price'))
                    ->aligncenter(),

                TextColumn::make('tax')
                    ->label(__('messages.purchase_medicine.tax_amount'))
                    ->formatStateUsing(function ($state) {
                        return $state . ' %';
                    })
                    ->aligncenter(),

                TextColumn::make('quantity')
                    ->label(__('messages.purchase_medicine.quantity'))
                    ->aligncenter(),

                TextColumn::make('amount')
                    ->label(__('messages.purchase_medicine.amount'))
                    ->formatStateUsing(function ($record, $state) {
                        if (empty($state)) {
                            return $record->medicines?->buying_price * $record?->quantity;
                        }

                        return $state;
                    })
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                    ->aligncenter(),
            ])
            ->paginated(false)
            ->emptyStateHeading(__('messages.custom_table_message.no_medicine_purchase'));
    }
}
