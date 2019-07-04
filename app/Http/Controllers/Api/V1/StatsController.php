<?php

namespace App\Http\Controllers\Api\V1;

use App\Fair;
use App\Http\Controllers\Controller;
use App\User;
use App\Company;
use App\CompanyJob;
use App\UserLogs;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function index()
    {
        $fairs     = Fair::count();
        $companies = Company::count();
        $compjobs  = CompanyJob::count();
        $users     = User::count();
        $user24hrs = UserLogs::where('created_at', '>=', Carbon::now()->subDay())->count();
        $logs      = UserLogs::select('fairs.name as fname','users.name as uname','user_logs.user_ip', 
                                      'user_logs.location','user_logs.device', 'user_logs.browser',
                                      'user_logs.referrer', 'user_logs.u_id', 'user_logs.created_at')
                               ->where('user_logs.created_at', '>=', Carbon::now()->subDay())
                               ->leftJoin('users', 'user_logs.user_id', '=', 'users.id')
                               ->leftJoin('fairs', 'user_logs.fair_id', '=', 'fairs.id')
                               ->get();
        $data = [
            'fairs'     => $fairs,
            'users'     => $users,
            'companies' => $companies,
            'compjobs'  => $compjobs,
            'logs'      => $logs,
            'user24hrs' => $user24hrs
        ];
        return response()->json([
            "code"   => 200,
            "status" => "success",
            "data"   => $data,
        ]);
    }
}
