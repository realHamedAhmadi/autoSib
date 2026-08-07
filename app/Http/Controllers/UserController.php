<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

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
            'national_code'=>'required|string|size:10',
            'is_admin'=>'nullable|in:0,1',
        ]);

        User::create($validated);
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
            'national_code' => 'required|digits:10',
            'is_admin' => 'nullable|in:0,1',
            'is_active' => 'nullable|in:0,1',
        ]);

        $user->update($validated);
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
}
