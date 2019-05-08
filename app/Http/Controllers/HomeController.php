<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $users = User::where('id','!=', Auth::id(1))->get();
        return view('home',['users'=>$users]);
    }
    public function impersonate($user_id){
        // echo $user_id; die;
        $user = User::find($user_id);
        Auth::user()->impersonate($user);
        return redirect()->route('home');
    }

    public function impersonate_leave(){
       Auth::user()->leaveImpersonation();
       return redirect()->route('home');
    }
}
