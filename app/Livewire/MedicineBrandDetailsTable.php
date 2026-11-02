<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Medicine;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;

class MedicineBrandDetailsTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;
    public $record;
    public function mount($record)
    {
        $this->record = $record;
    }
    protected function GetRecord()
    {
        return Medicine::query()
            ->where('category_id', $this->record->id)
            ->with('brand');
    }
    public function table(Table $table): Table
    {
        return $table
            ->query($this->GetRecord())
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('category.name')
                    ->label(__('messages.medicine.category'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('messages.medicine.medicine'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('selling_price')
                    ->label(__('messages.medicine.selling_price'))
                    ->formatStateUsing(fn($record) => getCurrencyIcon($record->currency_id) . $record->selling_price)
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('buying_price')
                    ->label(__('messages.medicine.buying_price'))
                    ->formatStateUsing(fn($record) => getCurrencyIcon($record->currency_id) . $record->buying_price)
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_medicine'));
    }
    public function render()
    {
        return view('livewire.medicine-brand-details-table');
    }
}
