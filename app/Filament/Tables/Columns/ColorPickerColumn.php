<?php

namespace App\Filament\Tables\Columns;

use Filament\Support\Components\Contracts\HasEmbeddedView;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Concerns\CanBeValidated;
use Filament\Tables\Columns\Concerns\CanUpdateState;
use Filament\Tables\Columns\Contracts\Editable;
use Illuminate\Support\Js;

class ColorPickerColumn extends Column implements Editable, HasEmbeddedView
{
    use CanBeValidated;
    use CanUpdateState;

    protected function setUp(): void
    {
        parent::setUp();

        $this->disabledClick();

        $this->rules(['string']);
    }

    public function toEmbeddedHtml(): string
    {
        $state = $this->getState();

        $attributes = $this->getExtraAttributeBag()
            ->merge([
                'x-data' => '{
                    error: undefined,
                    state: '.Js::from($state).',
                    isLoading: false,
                    async saveState() {
                        if (this.isLoading) return;

                        this.isLoading = true;

                        try {
                            await $wire.call(\'updateTableColumnState\', '.Js::from($this->getName()).', '.Js::from($this->getRecordKey()).', this.state);
                        } catch (error) {
                            this.error = error.message;
                        } finally {
                            this.isLoading = false;
                        }
                    }
                }',
            ], escape: false)
            ->class([
                'filament-tables-color-picker-column',
            ]);

        ob_start(); ?>

        <div
            <?= $attributes->toHtml() ?>
        >
            <div class="flex items-center space-x-2 relative">
                <div
                    class="w-6 h-6 rounded-md cursor-pointer"
                    style="background-color: <?= e($state ?? '#ffffff') ?>"
                    title="<?= e($state ?? '#ffffff') ?>"
                    x-on:click.stop="$refs.colorInput.click()"
                ></div>
                <span class="text-sm font-mono"><?= e($state ?? '#ffffff') ?></span>
                <input
                    type="color"
                    x-ref="colorInput"
                    x-model="state"
                    x-on:change="saveState()"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                />
            </div>
        </div>

        <?php return ob_get_clean();
    }

    public function validate(mixed $input): void
    {
        // No validation needed for color picker
    }
}
