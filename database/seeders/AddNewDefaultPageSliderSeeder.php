<?php

namespace Database\Seeders;

use App\Models\Slider;
use Illuminate\Database\Seeder;

class AddNewDefaultPageSliderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $getHomeSlider = Slider::whereId(1)->first();

        if (! $getHomeSlider) {
            $this->call(DefaultSliderSeeder::class);
        } else {
            $getHomeSlider->update(['page_type' => Slider::PAGE_HOME]);
        }

        $inputs = [
            [
                'page_type' => Slider::PAGE_ABOUT,
                'title' => 'About Our Clinic',
                'short_description' => 'Learn more about our care team, values, and patient-first approach.',
                'is_default' => true,
            ],
            [
                'page_type' => Slider::PAGE_SERVICES,
                'title' => 'Our Medical Services',
                'short_description' => 'Explore the care services designed around your health journey.',
                'is_default' => true,
            ],
            [
                'page_type' => Slider::PAGE_DOCTORS,
                'title' => 'Meet Our Doctors',
                'short_description' => 'Find experienced specialists who guide your care with confidence.',
                'is_default' => true,
            ],
            [
                'page_type' => Slider::PAGE_CONTACT,
                'title' => 'Contact Our Team',
                'short_description' => 'Reach out for booking help, service questions, and clinic details.',
                'is_default' => true,
            ],
            [
                'page_type' => Slider::PAGE_APPOINTMENT,
                'title' => 'Book Your Appointment',
                'short_description' => 'Choose your doctor, service, and available time slot in one place.',
                'is_default' => true,
            ],
            [
                'page_type' => Slider::PAGE_FAQ,
                'title' => 'Frequently Asked Questions',
                'short_description' => 'Find quick answers to common booking and care questions.',
                'is_default' => true,
            ],
            [
                'page_type' => Slider::PAGE_PRIVACY_POLICY,
                'title' => 'Privacy Policy',
                'short_description' => 'See how we collect, use, and protect your information.',
                'is_default' => true,
            ],
            [
                'page_type' => Slider::PAGE_TERMS_CONDITIONS,
                'title' => 'Terms & Conditions',
                'short_description' => 'Review the rules that guide how our website and services are used.',
                'is_default' => true,
            ],
        ];

        foreach ($inputs as $input) {
            $slider = Slider::updateOrCreate(
                ['page_type' => $input['page_type']],
                $input
            );

        }
    }
}
