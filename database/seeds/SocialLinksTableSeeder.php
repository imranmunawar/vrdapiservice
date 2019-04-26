<?php

use Illuminate\Database\Seeder;
use App\Sociallinks;

class SocialLinksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $link = new Sociallinks();
        $link->name = 'twiter';
        $link->save();
        $link = new Sociallinks();
        $link->name = 'facebook';
        $link->save();
        $link = new Sociallinks();
        $link->name = 'instagram';
        $link->save();
        $link = new Sociallinks();
        $link->name = 'linkdin';
        $link->save();
    }
}
