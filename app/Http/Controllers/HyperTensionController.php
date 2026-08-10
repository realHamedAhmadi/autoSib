<?php

namespace App\Http\Controllers;

use App\Data\Sib\User\SibUserSearchFilters;
use App\Services\AutomationService;
use App\Services\Sib\User\SibUserSearchService;
use App\Support\CareType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class HyperTensionController extends Controller
{
    protected $sickIds=[1005];
    public function __construct(
        protected readonly AutomationService $automationService
    )
    {
        Gate::authorize('allowedCare',CareType::HYPER_TENSION);
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
                idFamilyRelation: $request->family_relation,
                idBlockNumber: $request->service_groups,
                name: $request->name,
                family: $request->family,
                phone: $request->mobile,
                gender: $request->gender,
                idMarriageType: $request->marriage_types,
                countPerPage: $request->countPerPage,
                currentPageNumber: $request->page
            )
        );
        return view('hyper-tension.index',compact('users'));
    }

    function store(Request $request,)
    {
        $request->validate([
            'users'=>'required',
            'users.*.id'=>'required',
            'users.*.token'=>'required',
        ]);
        $run=$this->automationService->run($request->users,CareType::HYPER_TENSION);
        return redirect()->route('automation.runs.show',['run'=>$run->id]);
    }
}
