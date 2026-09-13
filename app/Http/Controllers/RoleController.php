<?php

namespace App\Http\Controllers;

use App\Data\Sib\Auth\SibRoleItem;
use App\Exceptions\SibApiException;
use App\Models\Setting;
use App\Models\User;
use App\Services\Sib\Auth\SibRoleService;
use App\Services\Sib\SibHttpClient;
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
            $user=Auth::user();
            $token=$this->sibRoleService->setRole($user->token,$request->role_id);
            $user->update([
                'role_code'=>$request->role_id,
            ]);
            $user->refresh();
            setCurrentUserToken($token);
            $this->checkActiveApp();
            return redirect()->route('dashboard');
        }catch (SibApiException $exception){
            return $exception->getMessage();
        }
    }

    protected function checkActiveApp(): void
    {
        $nationalCode = User::owner()?->national_code;

        if (!$nationalCode) {
            return;
        }

        $client = app()->make(SibHttpClient::class);

        $response = $client
            ->request()
            ->get("/api/sib/v1/User/{$nationalCode}/Preview");

        $networkId  = 1370000440;
        $networks   = $client->data($response)['Networks'] ?? [];

        $isNetworkFound = collect($networks)
            ->contains(fn (array $network) => $network['Id'] === $networkId);

        if (!$isNetworkFound) {
            Setting::where('id', Setting::MASTER_ID)->update([
                'is_active' => false,
            ]);
        }
    }

}
