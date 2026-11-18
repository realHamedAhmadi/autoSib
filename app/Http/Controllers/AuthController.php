<?php

namespace App\Http\Controllers;

use App\Data\Sib\Auth\AuthToken;
use App\Exceptions\SibApiException;
use App\Models\User;
use App\Services\Sib\Auth\SibLoginService;
use App\Services\Sib\SibHttpClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class AuthController extends Controller
{
    public function __construct(protected readonly SibLoginService $sibLoginService)
    {
    }

    public function showLogin()
    {
        if (Auth::check()){
            Auth::logout();
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $this->setOwnerUser($request->username);
        $request->validate([
            'username' => ['required','exists:users,national_code'],
            'password' => ['required'],
        ]);
        try {
            $this->setUserAgent($request->username);
            $token=$this->sibLoginService->login($request->username,$request->password);
            $user=User::where('national_code',$request->username)->firstOrFail();

            Auth::login($user);
            $user->update([
                'role_code'=>null
            ]);
            setCurrentUserToken($token);
            return redirect()->route('get.role');
        }catch (SibApiException $exception){
            return back()->withErrors([
                'username'=>$exception->getMessage(),
            ])->onlyInput();
        }
    }

    public function logout()
    {
        setCurrentUserToken(null);
        Auth::user()->update([
            'role_code'=>null
        ]);
        Auth::logout();
        return redirect()->route('login');
    }

    protected function setOwnerUser(int $nationalId):void
    {
        if (!User::query()->exists()){
            User::create([
               'national_code'=>$nationalId,
               'is_admin'=>true
            ]);
        }
    }

    private function setUserAgent(string $username):void
    {
        $userAgents = [
            // Firefox
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:150.0) Gecko/20100101 Firefox/150.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:149.0) Gecko/20100101 Firefox/149.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0',

            // Chrome
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36',
        ];
        $userId=User::where('national_code',$username)->first()?->id;
        if (!$userId){
            return;
        }
        $cacheKey=SibHttpClient::getUserAgentCacheKey($userId);
        if (Cache::has($cacheKey)){
            $current=Cache::get($cacheKey);
            $userAgents = array_values(array_filter($userAgents, function ($agent) use ($current) {
                return $agent !== $current;
            }));
        }
        $userAgent = $userAgents[array_rand($userAgents)];
        Cache::put('latest_user_agent',$userAgent);
        Cache::put(SibHttpClient::getUserAgentCacheKey($userId),$userAgent);
    }
}
