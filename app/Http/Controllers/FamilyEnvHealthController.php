<?php

namespace App\Http\Controllers;

use App\Models\FamilyEnvHealth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FamilyEnvHealthController extends Controller
{
    function index(Request $request)
    {
        $records = FamilyEnvHealth::whereRaw('1=1');

        if ($request->name)
            $records->whereHas('user',function ($q)use($request){
                $q->where('name','like','%'.$request->name.'%');
            });

        if ($request->national_code)
            $records->whereHas('user',function ($q)use($request){
                $q->where('national_code','like',$request->national_code.'%');
            });

        $records=$records->paginate(50);

        return view('family-env-health', compact('records'));
    }

    function store(Request $request)
    {
        $validator = Validator::make($request->all(), ['national_code' => 'required|numeric|digits:10']);
        if ($validator->fails())
            return back()->withErrors($validator->errors());

        $user = User::where('national_code', $request->national_code)->first();
        if (!$user)
            $user = User::create([
                'national_code' => $request->national_code
            ]);
        if (!$user->familyEnvHealth()->exists())
            $user->familyEnvHealth()->create($request->except('_token'));
        else{
            FamilyEnvHealth::where('user_id',$user->id)->first()->update($request->except('_token','national_code'));
            return back()->withErrors(['قبلا ایجاد شده، تغییرات انجام شد']);
        }

        return back();
    }

    function update(Request $request, $id)
    {

        foreach ($request->envs ?? [] as $key => $attrs) {
            $f = FamilyEnvHealth::find($key);
            $f->update($attrs);
        }

        return back();
    }
}
