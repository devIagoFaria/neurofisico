<?php

namespace App\Filament\Resources\Transactions;

use App\Enums\DoctorNavigationSort;
use BackedEnum;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Schemas\Schema;
use App\Enums\NavigationSort;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Schemas\Components\View;
use App\Filament\Resources\Transactions\Pages\ViewTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransactions;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Pages\CreateTransactions;
use App\Filament\Resources\Transactions\Schemas\TransactionsForm;
use App\Filament\Resources\Transactions\Tables\TransactionsTable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class TransactionsResource extends Resource
{
    protected static ?string $model = Transaction::class;

    public static function canViewAny(): bool
    {
        //return checkPermission('manage_transactions');
        return false;
    }

    protected static string|BackedEnum|null $navigationIcon = Phosphor::MoneyWavy;
    public static function getNavigationSort(): ?int
    {
        if (auth()->user()->hasRole('doctor') || auth()->user()->hasRole('patient')) {
            return DoctorNavigationSort::TRANSACTIONS->value;
        }
        return NavigationSort::TRANSACTIONS->value;
    }

    protected static ?string $recordTitleAttribute = 'Transactions';
    public static function getModelLabel(): string
    {
        return __('messages.transactions');
    }

    public static function form(Schema $schema): Schema
    {
        return TransactionsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransactions::route('/'),
            // 'create' => CreateTransactions::route('/create'),
            // 'edit' => EditTransactions::route('/{record}/edit'),
            'view' => ViewTransaction::route('/{record}'),
        ];
    }
}
