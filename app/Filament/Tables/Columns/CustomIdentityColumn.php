<?php

namespace App\Filament\Tables\Columns;

use Filafly\IdentityColumn\Tables\Columns\IdentityColumn as BaseIdentityColumn;
use Filament\Tables\Columns\Concerns\CanFormatState;

class CustomIdentityColumn extends BaseIdentityColumn
{
    use CanFormatState;

    protected string $view = 'filament.tables.columns.custom-identity-column';

    public static function make(?string $name = null): static
    {
        $column = parent::make($name);

        $column
            ->label('ID')
            ->sortable()
            ->searchable()
            ->toggleable(isToggledHiddenByDefault: false);

        return $column;
    }
}
