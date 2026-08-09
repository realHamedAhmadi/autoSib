<?php

namespace App\Http\Controllers;

use App\Data\Sib\Auth\AuthToken;
use App\Exceptions\SibApiException;
use App\Models\User;
use App\Services\Sib\Auth\SibLoginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
