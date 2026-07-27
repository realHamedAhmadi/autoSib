<?php

namespace App\Http\Controllers;

use App\Services\Sib\User\SibAdminUserService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    function index(SibAdminUserService $service)
    {
        $u=$service->getUserInfo();
        print_r($u);

        return view('home');
    }
}
