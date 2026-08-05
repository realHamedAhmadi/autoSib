<?php
use Illuminate\Support\Facades\Auth;
use App\Data\Sib\Auth\AuthToken;

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
