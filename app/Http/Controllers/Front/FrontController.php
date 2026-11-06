<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\AppBaseController;
use App\Models\ClinicSchedule;
use App\Models\Doctor;
use App\Models\DoctorSession;
use App\Models\Faq;
use App\Models\FrontPatientTestimonial;
use App\Models\Patient;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class FrontController extends AppBaseController
{
    /**
     * @return Application|Factory|View
     */
    public function medical(): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
    {
        return redirect('/login');

        $doctors = Doctor::with('user', 'specializations')->whereHas('user', function (Builder $query) {
            $query->where('status', User::ACTIVE);
        })->latest()->take(10)->get()->pluck('user.full_name', 'id');
        $heroSlider = Slider::with('media')->wherePageType(Slider::PAGE_HOME)->latest()->first();
        $frontMedicalServicesArray = Service::with('media')->whereStatus(Service::ACTIVE)->latest()->get()->toArray();
        $frontMedicalServices = array_chunk($frontMedicalServicesArray, 2);
        $frontPatientTestimonials = FrontPatientTestimonial::with('media')->latest()->take(6)->get();
        $aboutExperience = Setting::where('key', 'about_experience')->first();
        $patients = Patient::get();

        return view('filament.pages.auth.login',
            compact('doctors', 'heroSlider', 'frontMedicalServices', 'frontPatientTestimonials',
                'aboutExperience', 'patients'));
    }

    /**
     * @return Application|Factory|View
     */
    public function medicalAboutUs(): \Illuminate\View\View
    {
        $data = [];
        $data['doctorsCount'] = Doctor::with('user')->get()->where('user.status', true)->count();
        $data['patientsCount'] = Patient::get()->count();
        $data['servicesCount'] = Service::whereStatus(true)->get()->count();
        $data['specializationsCount'] = Specialization::get()->count();
        $clinicSchedules = ClinicSchedule::all();
        $setting = Setting::where('key', 'about_us_image')->first();
        $frontPatientTestimonials = FrontPatientTestimonial::with('media')->latest()->take(6)->get();
        $doctors = Doctor::with('user', 'appointments', 'specializations')->whereHas('user', function (Builder $query) {
            $query->where('status', User::ACTIVE);
        })->withCount('appointments')->orderBy('appointments_count', 'desc')->take(3)->get();

        // $heroSlider = $this->resolveHeroContent(Slider::PAGE_ABOUT, [
        //     'title' => getSettingValue('about_title') ?: __('messages.web.about_us'),
        //     'short_description' => getSettingValue('about_short_description'),
        //     'image' => getSettingValue('about_image_1') ?: asset('assets/front/images/home/home-page-image.png'),
        // ]);

        $heroSlider = Slider::with('media')->wherePageType(Slider::PAGE_ABOUT)->latest()->first();

        return view('fronts.medical_about_us',
            compact('doctors', 'data', 'setting', 'clinicSchedules', 'frontPatientTestimonials', 'heroSlider'));
    }

    /**
     * @return Application|Factory|View
     */
    public function medicalServices(): \Illuminate\View\View
    {
        $data = [];
        $serviceCategories = ServiceCategory::with('activatedServices')->withCount('services')->get();
        $setting = Setting::pluck('value', 'key')->toArray();
        $services = Service::with('media', 'serviceCategory')
            ->withCount('serviceDoctors')
            ->whereStatus(Service::ACTIVE)
            ->latest()
            ->get();
        $data['doctorsCount'] = Doctor::with('user')->get()->where('user.status', true)->count();
        $data['patientsCount'] = Patient::get()->count();
        $data['servicesCount'] = Service::whereStatus(true)->get()->count();
        $data['specializationsCount'] = Specialization::get()->count();
        $heroSlider = Slider::with('media')->wherePageType(Slider::PAGE_SERVICES)->latest()->first();

        return view('fronts.medical_services', compact('serviceCategories', 'setting', 'services', 'data', 'heroSlider'));
    }

    /**
     * @return Application|Factory|View
     */
    public function medicalAppointment(): \Illuminate\View\View
    {
        $faqs = Faq::latest()->get();

        $appointmentDoctors = Doctor::with('user')->whereIn('id',
            DoctorSession::pluck('doctor_id')->toArray())->get()->where('user.status',
                User::ACTIVE)->pluck('user.full_name', 'id');
        $heroSlider = Slider::with('media')->wherePageType(Slider::PAGE_APPOINTMENT)->latest()->first();

        return view('fronts.medical_appointment', compact('faqs', 'appointmentDoctors', 'heroSlider'));
    }

    /**
     * @return Application|Factory|View
     */
    public function medicalDoctors(): \Illuminate\View\View
    {
        $doctors = Doctor::with('specializations', 'user')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->whereHas('user', function (Builder $query) {
                $query->where('status', User::ACTIVE);
            })
            ->latest()
            ->take(9)
            ->get();

        $doctorStats = [
            'doctors_count' => $doctors->count(),
            'specializations_count' => $doctors->flatMap(fn (Doctor $doctor) => $doctor->specializations)->unique('id')->count(),
            'reviews_count' => $doctors->sum('reviews_count'),
            'average_rating' => round((float) ($doctors->avg('reviews_avg_rating') ?? 0), 1),
        ];
        $heroSlider = Slider::with('media')->wherePageType(Slider::PAGE_DOCTORS)->latest()->first();

        return view('fronts.medical_doctors', compact('doctors', 'doctorStats', 'heroSlider'));
    }

    /**
     * @return Application|Factory|View
     */
    public function medicalContact(): \Illuminate\View\View
    {
        $clinicSchedules = ClinicSchedule::all();
        $heroSlider = Slider::with('media')->wherePageType(Slider::PAGE_CONTACT)->latest()->first();

        return view('fronts.medical_contact', compact('clinicSchedules', 'heroSlider'));
    }

    /**
     * @return Application|Factory|View
     */
    public function termsCondition(): \Illuminate\View\View
    {
        $termConditions = Setting::pluck('value', 'key')->toArray();
        $heroSlider = Slider::with('media')->wherePageType(Slider::PAGE_TERMS_CONDITIONS)->latest()->first();

        return view('fronts.terms_conditions', compact('termConditions', 'heroSlider'));
    }

    /**
     * @return Application|Factory|View
     */
    public function privacyPolicy(): \Illuminate\View\View
    {
        $privacyPolicy = Setting::pluck('value', 'key')->toArray();
        $heroSlider = Slider::with('media')->wherePageType(Slider::PAGE_PRIVACY_POLICY)->latest()->first();

        return view('fronts.privacy_policy', compact('privacyPolicy', 'heroSlider'));
    }

    /**
     * @return Application|Factory|View
     */
    public function faq(): \Illuminate\View\View
    {
        $faqs = Faq::latest()->get();
        $heroSlider = Slider::with('media')->wherePageType(Slider::PAGE_FAQ)->latest()->first();

        return view('fronts.faq', compact('faqs', 'heroSlider'));
    }

    /**
     * @return mixed
     */
    public function changeLanguage(Request $request)
    {
        Session::put('languageName', $request->input('languageName'));

        return $this->sendSuccess(__('messages.flash.language_change'));
    }
}
