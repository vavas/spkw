<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('psa_users')->insert([
            //Create admin
            [
                'id' => 1,
                'identity' => 'f8a7b94a-6c56-4bc5-94bf-d5efd921a26c',
                'email' => 'user@user.com',
                'password' => bcrypt('12345678'),
                'role' => 'admin',
                'onboarding' => 0
            ],
            //Create brand
            [
                'id' => 2,
                'identity' => 'fd37b8f1-efc2-4eab-8dbd-955a3a2be879',
                'email' => 'alliance@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'brand',
                'onboarding' => 0
            ],
            [
                'id' => 3,
                'identity' => '80f4f251-036c-42f0-9218-11f9be42deaf',
                'email' => 'navi@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'brand',
                'onboarding' => 0
            ],
            [
                'id' => 4,
                'identity' => '890a77dc-a467-41b3-a503-a9f4aa324bd4',
                'email' => 'empire@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'brand',
                'onboarding' => 0
            ],
            [
                'id' => 5,
                'identity' => '266424ba-33e0-4418-8903-d73f202bc6e9',
                'email' => 'evil-genius@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'brand',
                'onboarding' => 0
            ],
            [
                'id' => 6,
                'identity' => '9627dbab-d461-4a3f-b2c8-ac4c3a7840d5',
                'email' => 'virtus-pro@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'brand',
                'onboarding' => 0
            ],
            // Create influencers
            [
                'id' => 7,
                'identity' => 'e7a4e86c-9077-4c98-b858-bcd45f385704',
                'email' => 'influencer_1@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 8,
                'identity' => '46ecdd12-52c0-4c96-9d1d-aaf63202b389',
                'email' => 'influencer_2@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 9,
                'identity' => 'f935e751-7bd8-4f91-b60b-6672f61ee38e',
                'email' => 'influencer_3@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 10,
                'identity' => '083da3bf-e9fc-458c-843b-4ff781d49226',
                'email' => 'influencer_4@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 11,
                'identity' => 'fb961c3a-d525-4e09-8690-54c155ac74fb',
                'email' => 'influencer_5@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 12,
                'identity' => 'e74a00b6-cef0-4978-a91e-67b8e30016f0',
                'email' => 'influencer_6@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 13,
                'identity' => 'd39bef06-01a1-4613-b49e-4088b8818bbd',
                'email' => 'influencer_7@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 14,
                'identity' => '7a6dca11-f32a-42d5-b732-7ec0300c4eb8',
                'email' => 'influencer_8@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 15,
                'identity' => 'e41d2d39-a835-417b-9678-adfb9f4c5ed7',
                'email' => 'influencer_9@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 16,
                'identity' => 'a9bb4b42-24aa-4530-b771-eb296194bd12',
                'email' => 'influencer_10@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 17,
                'identity' => '11232f2c-b9dd-40f4-b492-4567355b62e0',
                'email' => 'influencer_11@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 18,
                'identity' => '27dca64a-ec7a-4435-bf82-6090c66eb39c',
                'email' => 'influencer_12@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 19,
                'identity' => '01f04272-991e-49e4-b47e-09bfbcb927da',
                'email' => 'influencer_13@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 20,
                'identity' => '0c7b568f-1141-410f-ba92-40ebbe7df334',
                'email' => 'influencer_14@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 21,
                'identity' => '0ba25ced-dc64-42d7-950a-0db908ae7345',
                'email' => 'influencer_15@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 22,
                'identity' => 'fea23d38-e517-4cea-ba5b-05845915d1ad',
                'email' => 'influencer_16@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 23,
                'identity' => '47a2a0b8-31a8-4238-9c40-c001bf3ba5bf',
                'email' => 'influencer_17@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 24,
                'identity' => '1bfdaf8f-52dc-4c90-afc0-f7271add5941',
                'email' => 'influencer_18@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 25,
                'identity' => '3e4557bf-bab8-412d-9fa4-0b6384f0005f',
                'email' => 'influencer_19@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ],
            [
                'id' => 26,
                'identity' => '9c615ecc-72d8-4529-bfc2-a897b6aa6463',
                'email' => 'influencer_20@sparkwoo.com',
                'password' => bcrypt('12345678'),
                'role' => 'influencer',
                'onboarding' => 1
            ]
        ]);
    }
}
