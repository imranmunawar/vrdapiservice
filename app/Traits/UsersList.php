<?php 
namespace App\Traits;
use Illuminate\Http\Request;
use App\User;

trait UsersList 
{
  public function getUsers($type, $company_id = ''){
    $users = User::whereHas('roles', function ($query) use ($type) {
            $query->where('name', '=', $type);
        })->with('userSetting')->get();

        $userArrs = json_decode(json_encode($users), true);

        if (!empty($company_id)) {
            $filteredUsers = array_filter($userArrs, function ($item) use ($company_id) {
               if ($item['user_setting']['company_id'] == $company_id) {
                   return true;
               }
               return false;
            });

            return $filteredUsers;
        }
        return $users;
  }
  
}
