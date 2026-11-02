<?php

namespace App\Models;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\VisitPrescription
 *
 * @property int $id
 * @property int $visit_id
 * @property string $prescription_name
 * @property string $frequency
 * @property string $duration
 * @property mixed $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription query()
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription whereFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription wherePrescriptionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription whereVisitId($value)
 *
 * @mixin \Eloquent
 */
class VisitPrescription extends Model
{
    use HasFactory;

    protected $table = 'visit_prescriptions';

    public $fillable = [
        'visit_id',
        'prescription_name',
        'frequency',
        'duration',
        'description',
    ];

    protected $casts = [
        'visit_id' => 'integer',
        'prescription_name' => 'string',
        'frequency' => 'string',
        'duration' => 'string',
        'description' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'prescription_name' => 'required|max:121',
        'frequency' => 'required',
        'duration' => 'required',
    ];

    public static function schema(): array
    {
        return [
            Grid::make(1)
                ->schema([
                    TextInput::make('prescription_name')
                        ->label(__('messages.prescription.name'))
                        ->placeholder(__('messages.prescription.name'))
                        ->required(),
                    TextInput::make('frequency')
                        ->label(__('messages.frequency'))
                        ->placeholder(__('messages.frequency'))
                        ->required(),
                    TextInput::make('duration')
                        ->label(__('messages.prescription.duration'))
                        ->minValue(0)
                        ->placeholder(__('messages.prescription.duration'))
                        ->numeric()
                        ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                        ->required(),
                    Textarea::make('description')
                        ->label(__('messages.visit.description'))
                        ->placeholder(__('messages.visit.description')),
                ]),
        ];
    }
}
