<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(FrontdesksTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        // $this->call(CompanyTableSeeder::class);
    }
}
