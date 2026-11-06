<?php
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthorizePaymentController;
use App\Http\Controllers\Front\EnquiryController;
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\Front\SubscribeController;
use App\Http\Controllers\FrontCompatibilityController;
//use App\Http\Controllers\GeneratePatientSmartCardsController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\LiveConsultationController;
use App\Http\Controllers\MedicineBillController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\PaystackController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

Route::middleware('setLanguage')->group(function () {
    Route::get('qr-code/p/{id?}', function () {
        return view('patient_show');
    })->name('patient_show');
});

Route::prefix('admin')->middleware('auth', 'xss', 'checkUserStatus', 'checkImpersonateUser', 'setLanguage')->group(function () {
    //Route::get('smart_card-pdf/{id}', [GeneratePatientSmartCardsController::class, 'smartCardPdf'])->name('admin.smartCardPdf');

    Route::get('appointment-pdf/{id}', [AppointmentController::class, 'appointmentPdf'])->name('admin.appointmentPdf');
});

// Stripe route
Route::get('/medical-payment-success', [AppointmentController::class, 'paymentSuccess'])->name('medical-appointment-payment-success');
Route::get('/medical-payment-failed', [AppointmentController::class, 'handleFailedPayment'])->name('medical-appointment-failed-payment');

// Stripe route
Route::get('/doctor/medical-payment-success', [AppointmentController::class, 'paymentSuccess'])->name('medical-appointment-doctor-payment-success');
Route::get('/doctor/medical-payment-failed', [AppointmentController::class, 'handleFailedPayment'])->name('medical-appointment-doctor-failed-payment');

Route::middleware('setLanguage')->group(function () {
    Route::get('medicine-bills-pdf/{id}', [MedicineBillController::class, 'convertToPDF'])->name('medicine.bill.pdf');
    Route::get('prescription-pdf/{id}', [PrescriptionController::class, 'convertToPDF'])->name('prescriptions.pdf');
});

// Paypal Route
Route::get('paypal-onboard', [PaypalController::class, 'onBoard'])->name('paypal.init');
Route::get('paypal-payment-success', [PaypalController::class, 'success'])->name('paypal.success');
Route::get('paypal-payment-failed', [PaypalController::class, 'failed'])->name('paypal.failed');

// Authorize Route
Route::get('authorize-onboard', [AuthorizePaymentController::class, 'onboard'])->name('authorize.init');
Route::post('authorize-do-payment', [AuthorizePaymentController::class, 'pay'])->name('authorize.onboard');
Route::get('authorize-payment-failed', [AuthorizePaymentController::class, 'failed'])->name('authorize.failed');

// Paystack Route
Route::get('paystack-payment-success', [PaystackController::class, 'handleGatewayCallback'])->name('paystack.success');

// RazorPay Route
Route::match(['get', 'post'], 'razorpay-onboard', [RazorpayController::class, 'onBoard'])
    ->name('razorpay.init');
Route::get('razorpay-payment-success', [RazorpayController::class, 'paymentSuccess'])
    ->name('razorpay.success');
Route::get('razorpay-payment-failed', [RazorpayController::class, 'paymentFailed'])
    ->name('razorpay.failed');
Route::get('razorpay-payment-webhook', [RazorpayController::class, 'paymentSuccessWebHook'])
    ->name('razorpay.webhook');

Route::get('/stripe/success', [StripeController::class, 'success'])
    ->name('stripe.success');

Route::get('/stripe/cancel', [StripeController::class, 'cancel'])
    ->name('stripe.cancel');

// Zoom
Route::any('zoom/callback', [LiveConsultationController::class, 'zoomCallback']);

// Google Calendar
Route::get('google-auth', [GoogleCalendarController::class, 'oauth'])
    ->name('googleAuth');

Route::get('login/google/callback', [GoogleCalendarController::class, 'redirect'])
    ->name('googleCalendarCallback');

Route::middleware('setLanguage')->group(function () {
    Route::get('/', [FrontController::class, 'medical'])->name('medical');
    Route::get('/medical-about-us', [FrontController::class, 'medicalAboutUs'])->name('medicalAboutUs');
    Route::get('/medical-services', [FrontController::class, 'medicalServices'])->name('medicalServices');
    Route::get('/medical-appointment', [FrontController::class, 'medicalAppointment'])->name('medicalAppointment');
    Route::get('/medical-doctors', [FrontController::class, 'medicalDoctors'])->name('medicalDoctors');
    Route::get('/medical-contact', [FrontController::class, 'medicalContact'])->name('medicalContact');
    Route::get('/terms-conditions', [FrontController::class, 'termsCondition'])->name('terms.conditions');
    Route::get('/privacy-policy', [FrontController::class, 'privacyPolicy'])->name('privacy.policy');
    Route::get('/faqs', [FrontController::class, 'faq'])->name('front.faqs');
    // Route::get('qr-code/p/{id?}', [PatientQrCodeController::class,'show'])->name('patient_show');
});
Route::post('/change-language', [FrontController::class, 'changeLanguage'])->name('front.change.language');
Route::post('front-appointment-book', [AppointmentController::class, 'frontAppointmentBook'])->name('front.appointment.book');
Route::post('medical-appointment', [AppointmentController::class, 'frontHomeAppointmentBook'])->name('front.home.appointment.book');
Route::get('get-patient-name', [AppointmentController::class, 'getPatientName'])->name('get-patient-name');
Route::get('doctor-appointment/{doctor}', [AppointmentController::class, 'doctorBookAppointment'])->name('doctorBookAppointment');
Route::get('service-appointment/{service}', [AppointmentController::class, 'serviceBookAppointment'])->name('serviceBookAppointment');
Route::post('/enquiries', [EnquiryController::class, 'store'])->name('enquiries.store');
Route::post('/subscribe', [SubscribeController::class, 'store'])->name('subscribe.store');
Route::get('doctor-session-time', [FrontCompatibilityController::class, 'getDoctorSession'])->name('doctor-session-time');
Route::get('get-service', [FrontCompatibilityController::class, 'getService'])->name('get-service');
Route::get('get-charge', [FrontCompatibilityController::class, 'getCharge'])->name('get-charge');
Route::get('/manually-payment', [AppointmentController::class, 'manuallyPayment'])->name('manually-payment');

Route::get('cancel-appointment/{patient_id}/{appointment_unique_id}', [AppointmentController::class, 'cancelAppointment'])->name('cancelAppointment');

require __DIR__.'/auth.php';


