<?php

namespace App\Support;

enum CareType: string
{
    case DIABETIC='دیابت';
    case HYPER_TENSION='فشار خون';
    case YOUNG_PEOPLE='جوانان';
    case MIDDLE_AGED='میانسالان';
    case THE_ELDERLY='سالمندان';
    case RISK_ASSESSMENT='خطر سنجی';
    case MENTAL='روان, اجتماعی و مواد';
    case TRADITIONAL_MEDICINE='طب سنتی';
    case TEENAGERS='نوجوانان';
    case DART='ارزيابي آمادگي خانوار در برابر بلايا';

    public static function all(): array
    {
        $result = [];

        foreach (self::cases() as $case) {
            $result[$case->name] = $case->value;
        }

        return $result;
    }

    public static function keys(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

}
