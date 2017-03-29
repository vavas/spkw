<?php

use Illuminate\Database\Seeder;

class InfluencersCampaignTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('psa_influencers_campaign')->insert([
            [
                'id' => 1,
                'identity' => '39ba04c9-ecf2-4c27-88ae-0235546b2eaa',
                'campaign_id' => 'c26cc81f-9cff-44b2-bdca-66ecbf90145f',
                'influencer_id' => 'e7a4e86c-9077-4c98-b858-bcd45f385704',
                'app_brand' => 'approved',
                'app_influencer' => 'approved',
                'status' => 'application accepted',
                'compensation' => '100.00',
                'consideration' => '200',
                'message' => 'some message',
            ],
            [
                'id' => 2,
                'identity' => '7ddd4023-fe9d-4e1b-b876-dc49a86eeb1e',
                'campaign_id' => '9c615ecc-72d8-4529-bfc2-a897b6aa6463',
                'influencer_id' => 'e7a4e86c-9077-4c98-b858-bcd45f385704',
                'app_brand' => 'approved',
                'app_influencer' => 'approved',
                'status' => 'application accepted',
                'compensation' => '100.00',
                'consideration' => '200',
                'message' => 'some message',
            ],
            [
                'id' => 3,
                'identity' => '9fe34ab5-c1ac-44bc-8d62-c8356cd7476f',
                'campaign_id' => 'c26cc81f-9cff-44b2-bdca-66ecbf90145f',
                'influencer_id' => '46ecdd12-52c0-4c96-9d1d-aaf63202b389',
                'app_brand' => 'approved',
                'app_influencer' => 'approved',
                'status' => 'application accepted',
                'compensation' => '100.00',
                'consideration' => '200',
                'message' => 'some message',
            ],
            [
                'id' => 4,
                'identity' => 'dae818bb-df19-45f3-8752-30bd3687fcf0',
                'campaign_id' => 'c26cc81f-9cff-44b2-bdca-66ecbf90145f',
                'influencer_id' => '083da3bf-e9fc-458c-843b-4ff781d49226',
                'app_brand' => 'approved',
                'app_influencer' => 'approved',
                'status' => 'application accepted',
                'compensation' => '100.00',
                'consideration' => '200',
                'message' => 'some message',
            ],
            [
                'id' => 5,
                'identity' => 'd838aadb-3c1f-43fe-ac66-3546fc1f4efc',
                'campaign_id' => 'c26cc81f-9cff-44b2-bdca-66ecbf90145f',
                'influencer_id' => 'fb961c3a-d525-4e09-8690-54c155ac74fb',
                'app_brand' => 'approved',
                'app_influencer' => 'approved',
                'status' => 'application accepted',
                'compensation' => '100.00',
                'consideration' => '200',
                'message' => 'some message',
            ],
        ]);
    }
}
