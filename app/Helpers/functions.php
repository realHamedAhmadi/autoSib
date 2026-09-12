<?php

use App\Models\AutomationRunUser;
use Illuminate\Support\Facades\Auth;
use App\Data\Sib\Auth\AuthToken;


if (!function_exists('normalizeNumber')) {
    /**
     * Convert Persian/Arabic digits to English and remove non-digit characters.
     */
    function normalizeNumber(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        // Convert Persian and Arabic digits to English
        $value = strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        // Remove everything except digits
        return preg_replace('/\D+/', '', $value) ?? '';
    }
}

function getCurrentUserToken(int|null $userId=null):string|null
{
    if ($userId){
        return \App\Models\User::find($userId)?->token;
    }
    return Auth::user()?->token;
}

function setCurrentUserToken(?AuthToken $authToken,?string $userId=null)
{
    $user=Auth::user();
    if ($userId){
        $user=\App\Models\User::find($userId);
    }
    $user->token=$authToken?->token;
    $user->token_expires_at=$authToken?->expiresAt;
    $user->save();
    $user->refresh();
}

function getCurrentUser()
{
    return Auth::user();
}

function getCurrentUserId()
{
    return Auth::id();
}

function arrayRandom(array $array)
{
    return $array[array_rand($array)];
}

function getRemainingUserCareCount()
{
    $todayUserCount=AutomationRunUser::distinct('sib_user_id')
    ->whereHas('run',function ($q){
        $q->where('user_id',getCurrentUserId());
    })->whereDate('created_at',date('Y-m-d'))->count();
    $maxUserCount=getCurrentUser()?->max_user_care;
    if (!is_null($maxUserCount)){
        if ($todayUserCount>=$maxUserCount){
            return 0;
        }
        return $maxUserCount-$todayUserCount;
    }
    return null;
}
