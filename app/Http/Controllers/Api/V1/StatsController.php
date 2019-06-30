<?php

namespace App\Http\Controllers\Api\V1;

use App\Fair;
use App\Http\Controllers\Controller;
use App\User;

class StatsController extends Controller
{
    public function index()
    {
        $fairs = Fair::where('status','=','0')->count();
        $users = User::count();
        $data = [
            'fairs' => $fairs,
            'users' => $users
        ];
        return response()->json([
            "code"   => 200,
            "status" => "success",
            "data"   => $data,
        ]);
    }
}
