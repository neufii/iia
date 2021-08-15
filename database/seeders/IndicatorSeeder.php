<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Indicator; 

class IndicatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $indicatorDataList = [
            ['name'=>'Numbers and Operations: Grade 1','description'=>'วิเคราะห์และหาคำตอบของโจทย์ปัญหาและโจทย์ปัญหาระคนของจำนวนนับไม่เกินหนึ่งร้อยและศูนย์ พร้อมทั้งตระหนักถึงความสมเหตุสมผลของคำตอบ'],
            ['name'=>'Numbers and Operations: Grade 2','description'=>'วิเคราะห์และหาคำตอบของโจทย์ปัญหาและโจทย์ปัญหาระคนของจำนวนนับไม่เกินหนึ่งพันและศูนย์ พร้อมทั้งตระหนักถึงความสมเหตุสมผลของคำตอบ']
        ];

        foreach($indicatorDataList as $indicatorData){
            $indicator = new Indicator();
            $indicator->name = $indicatorData['name'];
            $indicator->description = $indicatorData['description'];
            $indicator->save();
        }
    }
}
