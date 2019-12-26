<?php 
namespace App\Traits;
use Illuminate\Http\Request;
use App\Company;
use Illuminate\Support\Facades\Mail;

trait UserEmail 
{
  public function generateEmail($request){
    $name     =  $request->fname.' '.$request->lname;
    $email    =  $request->email;
    $password =  $request->password;
    $role     =  $request->role;
    $loginUrl =  env('BACKEND_URL').'/login';

    if ($role == 'Admin') {
		Mail::send('emails.admin', 
			[
				'name'     => $name, 
				'email'    => $email, 
				'password' => $password,
                'loginUrl' => $loginUrl
			], function($message) use ($email,$name,$role){
		        $message->to($email, $name)->subject('Welcome '.$role);
		});
    }elseif($role == 'Organizer'){
        Mail::send('emails.organizer', 
            [
                'name'     => $name, 
                'email'    => $email, 
                'password' => $password,
                'loginUrl' => $loginUrl
            ], function($message) use ($email,$name){
    	    $message->to($email, $name)->subject('Your Account is created on virtual recruitment days as Organizer');
    	});
    }elseif($role == 'Company Admin'){
    	$company = Company::find($request->company_id);
    	$company_name = $company->company_name;
        Mail::send('emails.companyAdmin', 
            [
                'company_name' => $company_name, 
                'name'         => $name, 
                'email'        => $email, 
                'password'     => $password,
                'loginUrl'     => $loginUrl
            ], function($message) use ($email,$name)
        {
            $message->to($email, $name)->subject('Welcome! '.$name);
        });
    }elseif($role == 'Recruiter'){
    	$company = Company::find($request->company_id);
    	$company_name = $company->company_name;
        Mail::send('emails.companyRrecruiter', 
            [
                'company_name' => $company_name, 
                'name'     => $name, 
                'email'    => $email, 
                'password' => $password,
                'loginUrl' => $loginUrl
            ], function($message) use ($email,$name)
        {
            $message->to($email, $name)->subject('Welcome! '.$name);
        });
    }elseif($role == 'Receptionist'){
        Mail::send('emails.companyReceptionist', 
            [
                'name'     => $name, 
                'email'    => $email, 
                'password' => $password,
                'loginUrl' => $loginUrl
            ], function($message) use ($email,$name)
        {
            $message->to($email, $name)->subject('Welcome! '.$name);
        });
    }

}
  
}
