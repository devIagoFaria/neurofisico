<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Medicine;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
    
class MedicineCategoryDetailsTable extends Component implements HasForms, HasTable, HasActions
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
                TextColumn::make('name')
                    ->label(__('messages.medicine.medicine'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('brand.name')
                    ->label(__('messages.medicine.brand'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description')
                    ->label(__('messages.medicine.description'))
                    ->getStateUsing(fn($record) => $record->description ?? __('messages.common.n/a'))
                    ->sortable()
                    ->searchable()
                    ->limit(30),
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
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalHeading(__('messages.common.view') . ' ' . __('messages.medicine.medicine'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('messages.medicine.medicine') . ':'),
            
                                TextEntry::make('brand.name')
                                    ->label(__('messages.medicine.brand') . ':')
                                    ->default(__('messages.common.n/a')),
            
                                TextEntry::make('selling_price')
                                    ->label(__('messages.medicine.selling_price') . ':')
                                    ->formatStateUsing(
                                        fn ($record) => getCurrencyIcon($record->currency_id) . $record->selling_price
                                    ),
            
                                TextEntry::make('buying_price')
                                    ->label(__('messages.medicine.buying_price') . ':')
                                    ->formatStateUsing(
                                        fn ($record) => getCurrencyIcon($record->currency_id) . $record->buying_price
                                    ),

                                TextEntry::make('description')
                                    ->label(__('messages.medicine.description') . ':')
                                    ->default(__('messages.common.n/a'))
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ])
            ->emptyStateHeading(__('messages.custom_table_message.no_medicine'));
    }
    public function render()
    {
        return view('livewire.medicine-category-details-table');
    }
}
