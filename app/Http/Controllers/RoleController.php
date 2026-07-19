<?php

namespace App\Http\Controllers;

use App\Data\Sib\Auth\SibRoleItem;
use App\Exceptions\SibApiException;
use App\Services\Sib\Auth\SibRoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function __construct(protected readonly SibRoleService $sibRoleService)
    {
    }

    function getRole()
    {
        try {
            $roles=$this->sibRoleService->getRoles(Auth::user()->token);
            $roles = collect($roles)
                ->filter(function (SibRoleItem $item) {
                    return Str::contains($item->title, ['بهورز', 'مراقب']);
                })
                ->values()
                ->all();
            return view('auth.role',compact('roles'));
        }catch (SibApiException $exception){
            return $exception->getMessage();
        }
    }

    function setRole(Request $request)
    {
        $request->validate([
            'role_id'=>'required',
        ]);
        try {
            $token=$this->sibRoleService->setRole(Auth::user()->token,$request->role_id);
            $user=Auth::user();
            $user->token=$token->jwt;
            $user->token_expires_at=$token->expiresAt;
            $user->save();
            $user->refresh();
            return redirect()->route('dashboard');
        }catch (SibApiException $exception){
            return $exception->getMessage();
        }
    }
}
