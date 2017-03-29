<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(StatesTableSeeder::class);
        $this->call(InterestsTableSeeder::class);
        $this->call(VersionTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(BrandsTableSeeder::class);
        $this->call(InfluencersTableSeeder::class);
        $this->call(InterestInfluencerTableSeeder::class);
        $this->call(SocialAccountTableSeeder::class);
        $this->call(CampaignTableSeeder::class);
        $this->call(InfluencersCampaignTableSeeder::class);
        $this->call(InterestCampaignTableSeeder::class);
        $this->call(IOSTableSeeder::class);

        Model::reguard();
    }
}
