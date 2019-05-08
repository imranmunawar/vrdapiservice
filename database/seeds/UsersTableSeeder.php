<?php
use Illuminate\Database\Seeder;
use App\User;
use App\Role;
class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $organizer = Role::where('name', 'Organizser')->first();
        $company   = Role::where('name', 'Company')->first();
        $admin     = Role::where('name', 'Admin')->first();
        $nuser     = Role::where('name', 'User')->first();
        $user = new User();
        $user->first_name = 'imran';
        $user->last_name = 'khan';
        $user->name = 'Imran Khan';
        $user->email = 'imran@gmail.com';
        $user->password = bcrypt('geopak123');
        $user->plan_password = 'geopak123';
        $user->save();
        $user->roles()->attach($admin);
        $user = new User();
        $user->first_name = 'Ahmed';
        $user->last_name = 'khan';
        $user->name = 'Ahmed Khan';
        $user->email = 'ahmed@gmail.com';
        $user->password = bcrypt('geopak123');
        $user->plan_password = 'geopak123';
        $user->save();
        $user->roles()->attach($organizer);
        $user = new User();
        $user->first_name = 'Ali';
        $user->last_name = 'khan';
        $user->name = 'Ali Khan';
        $user->email = 'ali@gmail.com';
        $user->password = bcrypt('geopak123');
        $user->plan_password = 'geopak123';
        $user->save();
        $user->roles()->attach($company);
        $user = new User();
        $user->first_name = 'Umar';
        $user->last_name = 'khan';
        $user->name = 'Umar Khan';
        $user->email = 'umar@gmail.com';
        $user->password = bcrypt('geopak123');
        $user->plan_password = 'geopak123';
        $user->save();
        $user->roles()->attach($nuser);
    }
}