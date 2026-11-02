<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * App\Models\Slider
 *
 * @property int $id
 * @property string $title
 * @property string $short_description
 * @property int $is_default
 * @property-read string $slider_image
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read MediaCollection|Media[] $media
 * @property-read int|null $media_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Slider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Slider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Slider query()
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slider wherePageType($value)
 *
 * @mixin \Eloquent
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereIsDefault($value)
 */
class Slider extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    const SLIDER_IMAGE = 'image';

    public const PAGE_HOME = 1;

    public const PAGE_ABOUT = 2;

    public const PAGE_SERVICES = 3;

    public const PAGE_DOCTORS = 4;

    public const PAGE_CONTACT = 5;

    public const PAGE_APPOINTMENT = 6;

    public const PAGE_FAQ = 7;

    public const PAGE_PRIVACY_POLICY = 8;

    public const PAGE_TERMS_CONDITIONS = 9;

    /**
     * @var string[]
     */
    public static $rules = [
        'title' => 'required',
        'image' => 'required|mimes:jpeg,png,jpg',
        'short_description' => 'required',
    ];

    /**
     * @var string[]
     */
    public static $editRules = [
        'title' => 'required',
        'image' => 'nullable|mimes:jpeg,png,jpg',
        'short_description' => 'required|max:55',
    ];

    /**
     * @var string
     */
    protected $table = 'sliders';

    /**
     * @var string[]
     */
    protected $fillable = [
        'page_type',
        'title',
        'short_description',
        'is_default',
    ];

    protected $casts = [
        'page_type' => 'string',
        'title' => 'string',
        'short_description' => 'string',
        'is_default' => 'boolean',
    ];

    protected $appends = ['slider_image'];

    public static function pageTypeOptions(): array
    {
        return [
            self::PAGE_HOME => __('messages.web.medical'),
            self::PAGE_ABOUT => __('messages.web.about_us'),
            self::PAGE_SERVICES => __('messages.web.services'),
            self::PAGE_DOCTORS => __('messages.web.medical_doctors'),
            self::PAGE_CONTACT => __('messages.web.contact_us'),
            self::PAGE_APPOINTMENT => __('messages.web.medical_appointment'),
            self::PAGE_FAQ => __('messages.web.faqs'),
            self::PAGE_PRIVACY_POLICY => __('messages.privacy_policy'),
            self::PAGE_TERMS_CONDITIONS => __('messages.terms_conditions'),
        ];
    }

    public function scopeForPageType(Builder $query, string $pageType): Builder
    {
        return $query->where('page_type', $pageType);
    }

    public function getSliderImageAttribute(): string
    {
        /** @var Media $media */
        $media = $this->getMedia(self::SLIDER_IMAGE)->first();
        if (! empty($media)) {
            return $media->getFullUrl();
        }

        return '';
    }
}
