<?php

namespace App\Enums;

enum NavigationSort: int
{
    // staff
    case STAFF = 1;

        // doctor
    case DOCTOR_CLUSTER = 2;
    case DOCTORS = 3;
    case DOCTORS_SCHEDULES = 4;
    case DOCTORS_HOLIDAYS = 5;

        // patient
    case PATIENTS = 6;

        // patient smart card
    case SMART_PATIENT_CARDS = 7;
    case GENERATE_PATIENT_CARDS = 8;

        // appointment
    case APPOINTMENTS = 9;
    case MEDICINE_CATEGORIES = 10;
    case MEDICINE_BRANDS = 11;
    case MEDICINES = 12;
    case PURCHASES_MEDICINES = 13;
    case USED_MEDICINES = 14;
    case MEDICINE_BILLS = 15;
    case TRANSACTIONS = 16;
    case VISITS = 17;
    case SERVICES = 18;
    case SERVICE_CATEGORIES = 19;
    case SPECIALIZATIONS = 20;
    case ENQUIRIES = 21;
    case SUBSCRIBERS = 22;
    case CMS = 23;
    case BANNER = 24;
    case FAQS = 25;
    case FRONT_PATIENT_TESTIMONIALS = 26;
    case SETTINGS = 27;

        //setting
    case GENERAL = 28;
    case CONTACT_INFORMATION = 29;
    case CLINIC_SCHEDULES = 30;
    case ROLES = 31;
    case CURRENCIES = 32;
    case COUNTRIES = 33;
    case STATES = 34;
    case CITIES = 35;
}
