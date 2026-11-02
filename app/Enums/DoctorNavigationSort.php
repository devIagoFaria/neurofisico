<?php

namespace App\Enums;

enum DoctorNavigationSort: int
{
    case APPOINTMENTS = 1;
    case TRANSACTIONS = 2;
    case MY_SCHEDULE = 3;
    case VISITS = 4;
}