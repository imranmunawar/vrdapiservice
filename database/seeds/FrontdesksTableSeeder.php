<?php
use Illuminate\Database\Seeder;
use App\Frontdesk;
class FrontdesksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $frontdesk = new Frontdesk();
        $frontdesk->name = 'Presenter 1';
        $frontdesk->desk_image = 'assets/image/pre1.jpg';
        $frontdesk->desk_type = 0;
        $frontdesk->save();
        $frontdesk = new Frontdesk();
        $frontdesk->name = 'Presenter 2';
        $frontdesk->desk_image = 'assets/image/pre2.jpg';
        $frontdesk->desk_type = 0;
        $frontdesk->save();
        $frontdesk = new Frontdesk();
        $frontdesk->name = 'Receptionist 1';
        $frontdesk->desk_image = 'assets/image/rec1.jpg';
        $frontdesk->desk_type = 1;
        $frontdesk->save();
        $frontdesk = new Frontdesk();
        $frontdesk->name = 'Receptionist 2';
        $frontdesk->desk_image = 'assets/image/rec2.jpg';
        $frontdesk->desk_type = 1;
        $frontdesk->save();
    }
}