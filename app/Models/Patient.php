<?php

namespace App\Models;

use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;
use App\Models\SmartPatientCards;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Support\Colors\Color;
use ToneGabes\Filament\Icons\Enums\Phosphor;
use App\Filament\Forms\Components\PhoneInput;

/**
 * Class Patient
 *
 * @version July 29, 2021, 11:37 am UTC
 *
 * @property int $id
 * @property string $patient_unique_id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static PatientFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Patient newQuery()
 * @method static Builder|Patient onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Patient query()
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient wherePatientUniqueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereUserId($value)
 * @method static Builder|Patient withTrashed()
 * @method static Builder|Patient withoutTrashed()
 *
 * @mixin Model
 *
 * @property-read \App\Models\Address|null $address
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Appointment[] $appointments
 * @property-read int|null $appointments_count
 * @property-read string $profile
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|Media[] $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Patient permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient role($roles, $guard = null)
 */
class Patient extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasRoles;

    protected $table = 'patients';

    const PROFILE = 'profile';

    public const GOOGLE_JSON_FILE_PATH = 'google_json_file';

    const O_POSITIVE = 1;

    const A_POSITIVE = 2;

    const B_POSITIVE = 3;

    const AB_POSITIVE = 4;

    const O_NEGATIVE = 5;

    const A_NEGATIVE = 6;

    const B_NEGATIVE = 7;

    const AB_NEGATIVE = 8;

    const BLOOD_GROUP_ARRAY = [
        self::O_POSITIVE => 'O+',
        self::O_NEGATIVE => 'O-',
        self::A_POSITIVE => 'A+',
        self::A_NEGATIVE => 'A-',
        self::B_POSITIVE => 'B+',
        self::B_NEGATIVE => 'B-',
        self::AB_POSITIVE => 'AB+',
        self::AB_NEGATIVE => 'AB-',
    ];

    const MALE = 1;

    const FEMALE = 2;

    const ALL_PATIENT = 1;

    const ONLY_ONE_PATIENT = 2;

    const REMANING_PATIENT = 3;

    const ALL = 1;

    const TODAY = 2;

    const WEEK = 3;

    const MONTH = 4;

    const YEAR = 5;

    const PATIENT_FILTER = [
        self::ALL => 'All',
        self::TODAY => 'Today',
        self::WEEK => 'This Week',
        self::MONTH => 'This Month',
        self::YEAR => 'This Year',
    ];


    const STATUS = [
        self::ALL_PATIENT => 'All',
        self::ONLY_ONE_PATIENT => 'Active',
        self::REMANING_PATIENT => 'Deactive',
    ];

    public $fillable = [
        'patient_unique_id',
        'user_id',
        'template_id',
        'qr_code',
    ];

    protected $casts = [
        'patient_unique_id' => 'string',
        'user_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'patient_unique_id' => 'required|unique:patients,patient_unique_id|regex:/^\S*$/u',
        'first_name' => 'required',
        'last_name' => 'required',
        'email' => 'required|email|unique:users,email',
        'contact' => 'nullable|unique:users,contact',
        'password' => 'required|same:password_confirmation|min:6',
        'postal_code' => 'nullable',
        'profile' => 'nullable|mimes:jpeg,jpg,png|max:2000',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $editRules = [
        'first_name' => 'required',
        'last_name' => 'required',
        'profile' => 'nullable|mimes:jpeg,jpg,png',
    ];

    protected $appends = ['profile'];

    protected $with = ['media'];

    public static function generatePatientUniqueId(): string
    {
        $patientUniqueId = Str::random(8);
        while (true) {
            $isExist = self::wherePatientUniqueId($patientUniqueId)->exists();
            if ($isExist) {
                self::generatePatientUniqueId();
            }
            break;
        }

        return $patientUniqueId;
    }

    public function getProfileAttribute(): string
    {
        /** @var Media $media */
        $media = $this->getMedia(self::PROFILE)->first();
        if (! empty($media)) {
            return $media->getFullUrl();
        }
        $gender = $this->user->gender;
        if ($gender == self::FEMALE) {
            return asset('web/media/avatars/female.png');
        }

        return asset('web/media/avatars/male.png');
    }

    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'owner');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function patientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function smartPatientCard(): BelongsTo
    {
        return $this->belongsTo(SmartPatientCards::class, 'template_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::GOOGLE_JSON_FILE_PATH)
            ->useDisk('google_json_file')
            ->singleFile();
    }

    public static function schema(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    TextInput::make('first_name')
                        ->label(__('messages.patient.first_name'))
                        ->required()
                        ->maxLength(255)
                        ->placeholder(__('messages.patient.first_name')),
                    TextInput::make('last_name')
                        ->label(__('messages.patient.last_name'))
                        ->required()
                        ->maxLength(255)
                        ->placeholder(__('messages.patient.last_name')),

                    TextInput::make('email')
                        ->label(__('messages.user.email'))
                        ->email()
                        ->required()
                        ->unique(User::class, 'email', ignoreRecord: true)
                        ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? strtolower($state) : $state)
                        ->maxLength(255)
                        ->placeholder(__('messages.user.email')),

                    PhoneInput::make('contact')
                        ->label(__('messages.user.contact_number'))
                        ->placeholder(__('messages.user.contact_number')),

                    ToggleButtons::make('gender')
                        ->label(__('messages.staff.gender'))
                        ->required()
                        ->grouped()
                        ->colors([
                            1 => Color::Blue,
                            2 => Color::Pink,
                        ])
                        ->icons([
                            1 => Phosphor::GenderMale,
                            2 => Phosphor::GenderFemale,
                        ])
                        ->inline()
                        ->options([
                            1 => 'Male',
                            2 => 'Female',
                        ])
                        ->default(1),

                    Toggle::make('status')
                        ->label(__('messages.doctor.status'))
                        ->default(true)
                        ->inline(false),

                    TextInput::make('password')
                        ->label(__('messages.staff.password'))
                        ->password()
                        ->required()
                        ->revealable()
                        ->minLength(8)
                        ->maxLength(255)
                        ->dehydrateStateUsing(fn($state) => bcrypt($state))
                        ->dehydrated(fn($state) => filled($state))
                        ->confirmed()
                        ->placeholder(__('messages.staff.password')),

                    TextInput::make('password_confirmation')
                        ->label(__('messages.user.confirm_password'))
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(8)
                        ->maxLength(255)
                        ->dehydrated(false)
                        ->placeholder(__('messages.user.confirm_password')),
                ])
        ];
    }
}
