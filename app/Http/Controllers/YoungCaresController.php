<?php

namespace App\Http\Controllers;

use App\Data\Sib\User\SibUserSearchFilters;
use App\Models\AutomationRunUserCare;
use App\Models\Care;
use App\Services\AutomationService;
use App\Services\Sib\User\SibUserSearchService;
use App\Support\AutomationStatuses;
use App\Support\CareType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class YoungCaresController extends Controller
{

    protected int $ageFrom=18;
    protected int $ageTo=30;
    public function __construct(
        protected readonly AutomationService $automationService
    )
    {
    }

    function index(Request $request,SibUserSearchService $searchService)
    {
        $request->offsetSet('age_from',max($this->ageFrom,$request->age_from??18));
        $request->offsetSet('age_to',min($this->ageTo,$request->age_to??30));
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
                gender: $request->gender
            )
        );
        return view('young.index',compact('users'));
    }

    function store(Request $request,)
    {
        $request->validate([
            'users'=>'required',
            'users.*.id'=>'required',
            'users.*.token'=>'required',
        ]);
        $run=$this->automationService->run($request->users,CareType::YOUNG_PEOPLE);
        return redirect()->route('automation.show',['run'=>$run->id]);
    }
}
