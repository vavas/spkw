<?php

use Illuminate\Database\Seeder;

class IOSTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('psa_users')->insert([
            [
                'id' => 27,
                'identity' => 'd5b8dec3-1fd2-428d-93bc-0f04c0698cb3',
                'email' => '00000@gmail.com',
                'password' => bcrypt('1234567N'),
                'remember_token' => 'SozRbss0xbwAOMpkrGw2o7DBHqyIpUzPKJJH1gKxf3mGTJhcRWyxgzoEYqD3',
                'role' => 'influencer',
                'onboarding' => 1
            ]
        ]);

        DB::table('psa_influencers')->insert([
            [
                'id' => 27,
                'email' => '00000@gmail.com',
                'first_name' => 'Bob',
                'last_name' => 'Marley',
                'gender' => 'Male',
                'age' => 21,
                'image' => 'https://yt3.ggpht.com/-avJYI6bnVWk/AAAAAAAAAAI/AAAAAAAAAAA/LRZ5VqZ2NM8/s88-c-k-no-rj-c0xffffff/photo.jpg',
                'image_url' => 'https://yt3.ggpht.com/-avJYI6bnVWk/AAAAAAAAAAI/AAAAAAAAAAA/LRZ5VqZ2NM8/s88-c-k-no-rj-c0xffffff/photo.jpg',
                'location_state' => 'Washington',
                'location_city' => 'Seattle'
            ]
        ]);

        DB::table('psa_interest_influencer')->insert([
            [
                'id' => 21,
                'interest_identity' => '2aa30696-dfbb-4d5d-96f7-811b40701177',
                'influencer_identity' => 'd5b8dec3-1fd2-428d-93bc-0f04c0698cb3'
            ],
            [
                'id' => 22,
                'interest_identity' => '291c3a89-c4fe-4955-ab29-4e77a5df1fa3',
                'influencer_identity' => 'd5b8dec3-1fd2-428d-93bc-0f04c0698cb3'
            ],
            [
                'id' => 23,
                'interest_identity' => '854bfc7d-3101-48fe-8503-e8f0731c7d50',
                'influencer_identity' => 'd5b8dec3-1fd2-428d-93bc-0f04c0698cb3'
            ]
        ]);

        DB::table('psa_social_account')->insert([
            [
                'id' => 21,
                'user_identity' => 'd5b8dec3-1fd2-428d-93bc-0f04c0698cb3',
                'twitter_verified' => 0,
                'twitter_id' => '2301414984',
                'twitter_token' => '2301414984-ywADntSFxRiQYNwAoTGXknwYZhDvxk2XW10X9ZE',
                'twitter_screen_name' => 'RuslanMoskalen',
                'twitter_image' => 'http://pbs.twimg.com/profile_images/425261520679751680/6Fun0lNv_normal.jpeg',
                'twitter_followers' => 22,
                'twitter_onboard' => 1,
                'youtube_id' => '116237829939132566931',
                'youtube_token' => '{"refresh_token":"1\/RXCZHoMhBGXsNHVWGHnrE26s2vNxX60Dw38zBvSQv-8MEudVrK5jSpoR30zcRFq6","created":"116237829939132566931","access_token":"ya29.CjHzAtTGCLPsR-bTi9XNLWwucSsNMdFRRgkXb0ZVywTe7yGqPJAH_jpUVMZ4ds19lh-3","token_type":"Bearer","expires_in":"3600"}',
                'youtube_name' => 'Александр Жиленко',
                'youtube_image' => 'https://yt3.ggpht.com/-avJYI6bnVWk/AAAAAAAAAAI/AAAAAAAAAAA/LRZ5VqZ2NM8/s88-c-k-no-rj-c0xffffff/photo.jpg',
                'youtube_channel_id' => 'UCQZpKgyoglsyzfd-575-OKw',
                'youtube_channel_url' => 'https://www.youtube.com/channel/UCQZpKgyoglsyzfd-5',
                'youtube_onboard' => 1,
                'instagram_verified' => 1,
                'instagram_token' => '266626483.8d1e402.c7c028d52f4a4c2090cebe8aa3964463',
                'instagram_id' => '266626483',
                'instagram_name' => 'devil_2k14',
                'instagram_image' => 'https://scontent.cdninstagram.com/t51.2885-19/10471893_527700023996212_398528797_a.jpg',
                'instagram_followers' => 34,
                'instagram_onboard' => 1,
                'total_reach' => 468
            ]
        ]);

        DB::table('psa_influencers_campaign')->insert([
            [
                'id' => 6,
                'identity' => '2a68b723-093d-41af-9f9c-5c578f391fb8',
                'campaign_id' => 'c26cc81f-9cff-44b2-bdca-66ecbf90145f',
                'influencer_id' => 'd5b8dec3-1fd2-428d-93bc-0f04c0698cb3',
                'app_brand' => 'invitation',
                'app_influencer' => 'approved',
                'status' => 'invited',
                'compensation' => '100.00',
                'consideration' => '200',
                'message' => 'I want work',
            ],
            [
                'id' => 7,
                'identity' => '33d0a308-88e0-4db3-88f3-2fbb78503dcf',
                'campaign_id' => '9c615ecc-72d8-4529-bfc2-a897b6aa6463',
                'influencer_id' => 'd5b8dec3-1fd2-428d-93bc-0f04c0698cb3',
                'app_brand' => 'approved',
                'app_influencer' => 'invitation',
                'status' => 'applied',
                'compensation' => '100.00',
                'consideration' => '200',
                'message' => 'I want propose...',
            ],
            [
                'id' => 8,
                'identity' => 'c1c3f2fe-91f7-4b2e-a434-6c9bb753ab1e',
                'campaign_id' => 'e4ff07f6-db1f-4b94-95c2-b6016dff4616',
                'influencer_id' => 'd5b8dec3-1fd2-428d-93bc-0f04c0698cb3',
                'app_brand' => 'rejected',
                'app_influencer' => 'approved',
                'status' => 'application declined',
                'compensation' => '100.00',
                'consideration' => '200',
                'message' => 'I haven\'t time',
            ],
            [
                'id' => 9,
                'identity' => '7f36027d-9fa9-4228-b6f6-9fe824755169',
                'campaign_id' => '3ceed78a-7268-4143-a786-19c61c83d08d',
                'influencer_id' => 'd5b8dec3-1fd2-428d-93bc-0f04c0698cb3',
                'app_brand' => 'approved',
                'app_influencer' => 'rejected',
                'status' => 'application declined',
                'compensation' => '100.00',
                'consideration' => '200',
                'message' => 'You are lazy person',
            ],
            [
                'id' => 10,
                'identity' => '9bcad717-2157-4ed2-b484-79c3b4af869f',
                'campaign_id' => 'e41d2d39-a835-417b-9678-adfb9f4c5ed7',
                'influencer_id' => 'd5b8dec3-1fd2-428d-93bc-0f04c0698cb3',
                'app_brand' => 'approved',
                'app_influencer' => 'approved',
                'status' => 'application accepted',
                'compensation' => '100.00',
                'consideration' => '200',
                'message' => 'ok, welcome!',
            ],
            [
                'id' => 11,
                'identity' => '90228fa8-562a-455c-af37-116d1c0c43f2',
                'campaign_id' => 'a9bb4b42-24aa-4530-b771-eb296194bd12',
                'influencer_id' => 'd5b8dec3-1fd2-428d-93bc-0f04c0698cb3',
                'app_brand' => 'approved',
                'app_influencer' => 'approved',
                'status' => 'application accepted',
                'compensation' => '100.00',
                'consideration' => '200',
                'message' => 'some informative message',
            ]
        ]);
    }
}
