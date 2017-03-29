<?php

use Illuminate\Database\Seeder;

class InterestCampaignTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('psa_interest_campaign')->insert([
            [
                'id' => 1,
                'interest_id' => '9520a47b-0bf5-4488-9450-df1a328ef24a',
                'campaign_id' => 'c26cc81f-9cff-44b2-bdca-66ecbf90145f'
            ],
            [
                'id' => 2,
                'interest_id' => '9520a47b-0bf5-4488-9450-df1a328ef24a',
                'campaign_id' => 'fd37b8f1-efc2-4eab-8dbd-955a3a2be879'
            ],
            [
                'id' => 3,
                'interest_id' => '9520a47b-0bf5-4488-9450-df1a328ef24a',
                'campaign_id' => 'a24ae881-b96d-407e-8333-0ea6fcd47a13'
            ],
            [
                'id' => 4,
                'interest_id' => '9520a47b-0bf5-4488-9450-df1a328ef24a',
                'campaign_id' => '3ceed78a-7268-4143-a786-19c61c83d08d'
            ],
            [
                'id' => 5,
                'interest_id' => '9520a47b-0bf5-4488-9450-df1a328ef24a',
                'campaign_id' => 'e4ff07f6-db1f-4b94-95c2-b6016dff4616'
            ],
            [
                'id' => 6,
                'interest_id' => '9520a47b-0bf5-4488-9450-df1a328ef24a',
                'campaign_id' => '9c615ecc-72d8-4529-bfc2-a897b6aa6463'
            ],
            [
                'id' => 7,
                'interest_id' => '9520a47b-0bf5-4488-9450-df1a328ef24a',
                'campaign_id' => '3e4557bf-bab8-412d-9fa4-0b6384f0005f'
            ],
            [
                'id' => 8,
                'interest_id' => '9520a47b-0bf5-4488-9450-df1a328ef24a',
                'campaign_id' => '1bfdaf8f-52dc-4c90-afc0-f7271add5941'
            ],
            [
                'id' => 9,
                'interest_id' => '9520a47b-0bf5-4488-9450-df1a328ef24a',
                'campaign_id' => '47a2a0b8-31a8-4238-9c40-c001bf3ba5bf'
            ],
            [
                'id' => 10,
                'interest_id' => '9520a47b-0bf5-4488-9450-df1a328ef24a',
                'campaign_id' => 'fea23d38-e517-4cea-ba5b-05845915d1ad'
            ],
            [
                'id' => 11,
                'interest_id' => '9520a47b-0bf5-4488-9450-df1a328ef24a',
                'campaign_id' => '0c7b568f-1141-410f-ba92-40ebbe7df334'
            ],
            [
                'id' => 12,
                'interest_id' => '9520a47b-0bf5-4488-9450-df1a328ef24a',
                'campaign_id' => '01f04272-991e-49e4-b47e-09bfbcb927da'
            ],
            [
                'id' => 13,
                'interest_id' => '9520a47b-0bf5-4488-9450-df1a328ef24a',
                'campaign_id' => '27dca64a-ec7a-4435-bf82-6090c66eb39c'
            ],
            [
                'id' => 14,
                'interest_id' => '9520a47b-0bf5-4488-9450-df1a328ef24a',
                'campaign_id' => 'a9bb4b42-24aa-4530-b771-eb296194bd12'
            ],
            [
                'id' => 15,
                'interest_id' => '9520a47b-0bf5-4488-9450-df1a328ef24a',
                'campaign_id' => 'e41d2d39-a835-417b-9678-adfb9f4c5ed7'
            ]
        ]);
    }
}
