<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\CareType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    function index()
    {
        $authUser = Auth::user();

        $users = User::query()
            ->when(
                ! $authUser->isOwner(),
                function ($query) use ($authUser) {
                    $query->where(function ($query) use ($authUser) {
                        $query
                            ->where('id', $authUser->id)
                            ->orWhere('is_admin', false);
                    });
                }
            )
            ->get();
        return view('user.index',compact('users'));
    }

    function create()
    {
        return view('user.create');
    }

    function store(Request $request)
    {
        if ($request->is_admin){
            Gate::authorize('owner');
        }
        $validated=$request->validate([
            'name'=>'nullable|string|min:3|max:32',
            'national_code'=>'required|numeric|digits:10|unique:users,national_code',
            'max_user_care'=>'nullable|numeric',
            'care_sleep_time'=>'required|numeric|min:15',
            'is_admin'=>'nullable|in:0,1',
            'allowed_cares'=>'nullable|array',
            'allowed_cares.*'=>['required', Rule::in(array_column(CareType::cases(), 'name'))],
        ]);

        $user=User::create($validated);
        $this->setAllowedCares($user,$request);
        return redirect()->route('users.index');
    }

    function edit(User $user)
    {
        if ($user->isOwner()){
            Gate::authorize('owner');
        }
        if ($user->id!=Auth::id() && !Auth::user()->isOwner()){
            abort(403);
        }
        return view('user.edit',compact('user'));
    }

    function update(Request $request,User $user)
    {
        if (in_array($request->is_admin,['0','1'],true) || $user->isOwner()){
            Gate::authorize('owner');
        }
        if ($user->id!==Auth::id() && !Auth::user()->isOwner()){
            abort(403);
        }
        if ($user->isOwner()){
            $request->offsetSet('is_admin','1');
            $request->offsetSet('is_active','1');
        }
        $validated = $request->validate([
            'name' => 'nullable|string|min:3|max:32',
            'national_code'=>'required|numeric|digits:10|unique:users,national_code,'.$user->id,
            'max_user_care'=>'nullable|numeric',
            'care_sleep_time'=>'required|numeric|min:15',
            'is_admin' => 'nullable|in:0,1',
            'is_active' => 'nullable|in:0,1',
            'allowed_cares'=>'nullable|array',
            'allowed_cares.*'=>['required', Rule::in(array_column(CareType::cases(), 'name'))],
        ]);

        $user->update($validated);
        $this->setAllowedCares($user,$request);
        return redirect()->route('users.index');
    }

    function destroy(User $user)
    {
        if ($user->isOwner()){
            return back();
        }

        if ($user->isAdmin()){
            Gate::authorize('owner');
        }

        $user->delete();
        return back();
    }

    protected function setAllowedCares($user,$req)
    {
        $user->allowedCares()->delete();
        if ($req->allowed_cares) {
            foreach ($req->allowed_cares as $care) {
                $user->allowedCares()->create([
                    'type'=>$care
                ]);
            }
        }
    }
}
