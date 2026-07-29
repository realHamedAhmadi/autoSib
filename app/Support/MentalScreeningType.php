<?php

namespace App\Support;

enum MentalScreeningType: string
{
    case NEGATIVE = 'negative';
    case POSITIVE_ANXIETY = 'anxiety';
    case POSITIVE_DEPRESSION = 'depression';

}
