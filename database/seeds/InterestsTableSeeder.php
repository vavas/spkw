<?php

use Illuminate\Database\Seeder;

class InterestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('psa_interests')->insert([
            [
                'id' => 1,
                'identity' => '2aa30696-dfbb-4d5d-96f7-811b40701177',
                'interest_name' => 'Beauty',
                'status' => 'active',
            ],
            [
                'id' => 2,
                'identity' => '291c3a89-c4fe-4955-ab29-4e77a5df1fa3',
                'interest_name' => 'Art',
                'status' => 'active',
            ],
            [
                'id' => 3,
                'identity' => '854bfc7d-3101-48fe-8503-e8f0731c7d50',
                'interest_name' => 'Bodybuilding',
                'status' => 'active',
            ],
            [
                'id' => 4,
                'identity' => '6127b81e-a020-4467-a1c0-bfe79ccefce9',
                'interest_name' => 'Books',
                'status' => 'active',
            ],
            [
                'id' => 5,
                'identity' => '9520a47b-0bf5-4488-9450-df1a328ef24a',
                'interest_name' => 'Comedy',
                'status' => 'active',
            ],
            [
                'id' => 6,
                'identity' => 'c809911b-65cb-4386-8d01-d12c6421d010',
                'interest_name' => 'Comic/Anime',
                'status' => 'active',
            ],
            [
                'id' => 7,
                'identity' => '7931a7eb-0777-488c-8ff1-6dcbd6644af9',
                'interest_name' => 'Dating',
                'status' => 'active',
            ],
            [
                'id' => 8,
                'identity' => '622ac720-19b8-4cf8-83ee-7404e3d3788f',
                'interest_name' => 'Fashion',
                'status' => 'active',
            ],
            [
                'id' => 9,
                'identity' => '6f5f0312-217b-4347-bcec-8cb0db23b417',
                'interest_name' => 'Fitness',
                'status' => 'active',
            ],
            [
                'id' => 10,
                'identity' => 'be7e3618-f02f-4eb7-b195-bfaf990fedb4',
                'interest_name' => 'Food',
                'status' => 'active',
            ],
            [
                'id' => 11,
                'identity' => '37b36db2-f8c0-4257-9cbd-e8a59b949417',
                'interest_name' => 'Gaming',
                'status' => 'active',
            ],
            [
                'id' => 12,
                'identity' => 'd700c572-9ed9-47c8-b84c-51539d3c63cf',
                'interest_name' => 'Gardening',
                'status' => 'active',
            ],
            [
                'id' => 13,
                'identity' => 'e115237f-73c4-4327-893e-cbf9c597eb00',
                'interest_name' => 'Home Decor',
                'status' => 'active',
            ],
            [
                'id' => 14,
                'identity' => '58493aee-89b2-469c-8c55-a7f04e8d4e86',
                'interest_name' => 'Jewlery',
                'status' => 'active',
            ],
            [
                'id' => 15,
                'identity' => '21b4eeaa-c4e4-483a-97c6-fd4b4260b64d',
                'interest_name' => 'Kids',
                'status' => 'active',
            ],
            [
                'id' => 16,
                'identity' => '100dc4a5-f2c7-471f-a5da-ccc90a506f5a',
                'interest_name' => 'Lifestyle',
                'status' => 'active',
            ],
            [
                'id' => 17,
                'identity' => 'f865f904-61d5-4fc5-9222-69f2253c7a95',
                'interest_name' => 'Men\'s Health',
                'status' => 'active',
            ],
            [
                'id' => 18,
                'identity' => 'aa953ad2-2e69-4c37-80de-510523fe7d8e',
                'interest_name' => 'Movies',
                'status' => 'active',
            ],
            [
                'id' => 19,
                'identity' => '3cde7795-2d9e-48e4-bfe8-15a5c7dd3075',
                'interest_name' => 'Music',
                'status' => 'active',
            ],
            [
                'id' => 20,
                'identity' => 'b7ff8986-9a12-4c7c-bd76-3732c6ec9664',
                'interest_name' => 'Outdoors',
                'status' => 'active',
            ],
            [
                'id' => 21,
                'identity' => '9d2c7895-4057-4f3c-86c1-b36b33d6ef95',
                'interest_name' => 'Pets',
                'status' => 'active',
            ],
            [
                'id' => 22,
                'identity' => 'fdb4ca73-9d8e-4a85-8faf-29c02f3ed727',
                'interest_name' => 'Politics',
                'status' => 'active',
            ],
            [
                'id' => 23,
                'identity' => '456a6cce-2e00-4f4a-b42d-89c12daaf2d9',
                'interest_name' => 'Pregnancy',
                'status' => 'active',
            ],
            [
                'id' => 24,
                'identity' => 'aca57804-85a1-467d-a37d-23df2903a69b',
                'interest_name' => 'Product Review',
                'status' => 'active',
            ],
            [
                'id' => 25,
                'identity' => '83296c3e-cb36-4073-98e0-133dde80d034',
                'interest_name' => 'Sports',
                'status' => 'active',
            ],
            [
                'id' => 26,
                'identity' => '16f6e3e1-f534-4fc7-8973-ff4426141d44',
                'interest_name' => 'Tech',
                'status' => 'active',
            ],
            [
                'id' => 27,
                'identity' => 'faee3a20-9f35-4c7d-a6da-ef9792e93c9c',
                'interest_name' => 'Travel',
                'status' => 'active',
            ]
        ]);
    }
}
