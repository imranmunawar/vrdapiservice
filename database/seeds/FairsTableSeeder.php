<?php
use Illuminate\Database\Seeder;
use App\Fair;
class FairsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fair = new Fair();
        $fair->name = 'Demo 1';
        $fair->short_name = 'demo-1';
        $fair->phone = '03064622487';
        $fair->email = 'imran@gmail.com';
        $fair->presenter_id = 1;
        $fair->organiser_id = 2;
        $fair->receptionist_id = 3;
        $fair->fair_image = 'test.png';
        $fair->fair_video = 'test.mp4';
        $fair->website = 'www.test.com';
        $fair->fair_type = 0;
        $fair->status = 0;
        $fair->save();

        $fair = new Fair();
        $fair->name = 'Demo 2';
        $fair->short_name = 'demo-2';
        $fair->phone = '03064622487';
        $fair->email = 'imran@gmail.com';
        $fair->presenter_id = 2;
        $fair->organiser_id = 2;
        $fair->receptionist_id = 4;
        $fair->fair_image = 'test.png';
        $fair->fair_video = 'test.mp4';
        $fair->website = 'www.test.com';
        $fair->fair_type = 0;
        $fair->status = 0;
        $fair->save();
    }
}