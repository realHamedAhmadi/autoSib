<?php

namespace App\Http\Controllers;

use App\Services\Sib\User\SibUserService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    function index(SibUserService $service)
    {
        $u=$service->getUserInfo(\Illuminate\Support\Facades\Auth::user()->token);
        print_r($u);

        return view('home');
    }
}
