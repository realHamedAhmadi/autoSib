<?php

namespace App\Http\Controllers;

use App\Data\Sib\User\SibUserSearchFilters;
use App\Services\AutomationService;
use App\Services\Sib\User\SibUserSearchService;
use App\Support\CareType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MentalCaresController extends Controller
{
    protected int $ageFrom=18;
    public function __construct(
        protected readonly AutomationService $automationService
    )
    {
        Gate::authorize('allowedCare',CareType::MENTAL);
    }

    function index(Request $request,SibUserSearchService $searchService)
    {
        $request->offsetSet('age_from',max($this->ageFrom,$request->age_from??$this->ageFrom));
        $users=$searchService->search(
            getCurrentUserToken(),
            new SibUserSearchFilters(
                nationalId: $request->national_id,
                fromAgeYears: $request->age_from,
                toAgeYears: $request->age_to,
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
        $careType=CareType::MENTAL;
        return view('mental.index',compact('users','careType'));
    }

    function store(Request $request,)
    {
        $request->validate([
            'users'=>'required',
            'users.*.id'=>'required',
            'users.*.token'=>'required',
        ]);
        $run=$this->automationService->run($request->users,CareType::MENTAL);
        return redirect()->route('automation.runs.show',['run'=>$run->id]);
    }
}
