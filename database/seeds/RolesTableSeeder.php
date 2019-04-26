<?php
use Illuminate\Database\Seeder;
use App\Role;
class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_user = new Role();
        $role_user->name = 'Admin';
        $role_user->status = 0;
        $role_user->save();
        $role_author = new Role();
        $role_author->name = 'User';
        $role_user->status = 0;
        $role_author->save();
        $role_admin = new Role();
        $role_admin->name = 'Organizser';
        $role_user->status = 0;
        $role_admin->save();
        $role_admin = new Role();
        $role_admin->name = 'Receptionist';
        $role_user->status = 0;
        $role_admin->save();
        $role_admin = new Role();
        $role_admin->name = 'Company';
        $role_user->status = 0;
        $role_admin->save();
    }
}