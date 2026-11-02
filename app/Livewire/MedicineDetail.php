<?php

namespace App\Livewire;

use Filament\Tables\Table;
use App\Models\SaleMedicine;
use Filament\Widgets\TableWidget;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class MedicineDetail extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public $record;

    public function mount($record)
    {
        $this->record = $record->id;
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => SaleMedicine::query()->where('medicine_bill_id', $this->record))
            ->columns([
                TextColumn::make('medicine.name')
                    ->label(__('messages.medicine_bills.item_name')),
                TextColumn::make('sale_quantity')
                    ->label(__('messages.medicine.quantity'))
                    ->alignCenter(),
                TextColumn::make('sale_price')
                    ->label(__('messages.medicine_bills.price'))
                    ->formatStateUsing(fn($record) => getCurrencyIcon($record->currency_id) . $record->sale_price)
                    ->alignCenter(),
                TextColumn::make('tax')
                    ->label(__('messages.purchase_medicine.tax'))
                    ->alignCenter()
                    ->formatStateUsing(function ($state) {
                        return $state . '%';
                    }),
                TextColumn::make('amount')
                    ->label(__('messages.purchase_medicine.amount'))
                    ->alignCenter()
                    ->formatStateUsing(function ($record) {
                        $currencyIcon = getCurrencyIcon($record->currency_id);
                        $amount = $record->sale_price * $record->sale_quantity;
                        return $currencyIcon . $amount;
                    }),
            ])
            ->paginated(false)
            ->emptyStateHeading(__('messages.custom_table_message.no_medicine'));
    }
}
