<?php

namespace App\Http\Controllers;

use App\Data\Sib\User\SibUserSearchFilters;
use App\Services\AutomationService;
use App\Services\Sib\User\SibUserSearchService;
use App\Support\CareType;
use Illuminate\Http\Request;

class TheElderlyController extends Controller
{
    protected int $ageFrom=60;
    public function __construct(
        protected readonly AutomationService $automationService
    )
    {
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
                idBlockNumber: $request->service_groups,
                name: $request->name,
                family: $request->family,
                phone: $request->mobile,
                gender: $request->gender,
                countPerPage: $request->countPerPage,
                currentPageNumber: $request->page,
            )
        );
        return view('elderly.index',compact('users'));
    }

    function store(Request $request,)
    {
        $request->validate([
            'users'=>'required',
            'users.*.id'=>'required',
            'users.*.token'=>'required',
        ]);
        $run=$this->automationService->run($request->users,CareType::THE_ELDERLY);
        return redirect()->route('automation.runs.show',['run'=>$run->id]);
    }
}
