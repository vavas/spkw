<?php

use Illuminate\Database\Seeder;

class VersionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('psa_version')->insert([
            [
                'bundle_id' => 'com.sparkwoo.aphone',
                'minimum_version' => '1.1.0',
                'current_version' => '1.10.0',
                'store_url' => 'iTunes URL',
            ],
            [
                'bundle_id' => 'com.sparkwoo.iphone',
                'minimum_version' => '1.0.0',
                'current_version' => '1.5.0',
                'store_url' => 'iTunes URL',
            ]
        ]);
    }
}
