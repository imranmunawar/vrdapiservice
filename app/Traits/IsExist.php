<?php 

namespace App\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request; 
use App\User;

trait IsExist
{
	 /**
     * Trait implementation.
     *
     * @var IsExist
     */

	// Check If email is already exist in database
    public function checkUserEmail(Request $request)
    {
    	$user = new User;
		$result   = $user->IsEmailExist($request->email,$request->id);
		$res = ($result > 0 ? 'false' : 'true');

        return response()->json($res);
    }   
}