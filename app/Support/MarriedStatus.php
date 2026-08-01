<?php

namespace App\Support;

enum MarriedStatus:int
{
    case UNKNOWN = 0;       // نامشخص
    case DIVORCED = 1;      // طلاق گرفته
    case MARRIED = 2;       // متأهل
    case SINGLE = 3;        // مجرد
    case WIDOWED = 4;       // همسر فوت شده
    case NOT_APPLICABLE = 5; // موضوعیت ندارد
}
