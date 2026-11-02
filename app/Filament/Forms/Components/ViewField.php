<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class ViewField extends Field
{
    protected string $previewView = '';

    protected array $previewViewData = [];

    protected string $viewIdentifier = 'viewField';

    public function previewView(string $view): static
    {
        $this->previewView = $view;

        return $this;
    }

    public function previewViewData(array $data): static
    {
        $this->previewViewData = $data;

        return $this;
    }

    public function getView(): string
    {
        return $this->previewView ?: 'filament.forms.components.view-field';
    }

    public function getViewData(): array
    {
        return array_merge(
            $this->previewViewData,
            ['getState' => fn () => $this->getLivewire()->form->getRawState()]
        );
    }
}
