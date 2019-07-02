<?php

namespace App\Http\Controllers\Api\V1;

use App\Fair;
use App\Http\Controllers\Controller;
use App\User;
use App\Company;
use App\CompanyJob;

class StatsController extends Controller
{
    public function index()
    {
        $fairs     = Fair::where('status','=','0')->count();
        $companies = Company::count();
        $compjobs  = CompanyJob::count();
        $users     = User::count();
        $data = [
            'fairs'     => $fairs,
            'users'     => $users,
            'companies' => $companies,
            'compjobs'  => $compjobs
        ];
        return response()->json([
            "code"   => 200,
            "status" => "success",
            "data"   => $data,
        ]);
    }
}
