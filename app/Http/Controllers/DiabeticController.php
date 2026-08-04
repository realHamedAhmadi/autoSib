<?php

namespace App\Http\Controllers;

use App\Data\Sib\User\SibUserSearchFilters;
use App\Jobs\ProcessCareJob;
use App\Services\AutomationService;
use App\Services\Sib\Care\SibCareService;
use App\Services\Sib\User\SibUserSearchService;
use App\Services\Sib\User\SibUserService;
use App\Support\CareType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiabeticController extends Controller
{
    protected $sickIds=[1000,1001];
    public function __construct(
        protected readonly AutomationService $automationService
    )
    {
    }
    function index(Request $request,SibUserSearchService $searchService)
    {
        $users=$searchService->search(
            getCurrentUserToken(),
            new SibUserSearchFilters(
                nationalId: $request->national_id,
                fromAgeYears: $request->age_from,
                toAgeYears: $request->age_to,
                idSick: $this->sickIds,
                idBlockNumber: $request->service_groups,
                name: $request->name,
                family: $request->family,
                phone: $request->mobile,
                gender: $request->gender
            )
        );
        return view('diabetic.index',compact('users'));
    }

    function store(Request $request,)
    {
        $request->validate([
            'users'=>'required',
            'users.*.id'=>'required',
            'users.*.token'=>'required',
        ]);
        $run=$this->automationService->run($request->users,CareType::DIABETIC);
        return redirect()->route('automation.show',['run'=>$run->id]);
    }
}
