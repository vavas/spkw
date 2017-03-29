<?php

use Illuminate\Database\Seeder;

class BrandsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('psa_brands')->insert([
            [
                'id' => 2,
                'identity' => '68d87a0e-b72b-4721-bb04-50392bd105bd',
                'email' => 'alliance@sparkwoo.com',
                'first_name' => 'John',
                'last_name' => 'White',
                'brand_name' => 'Coca-cola',
                'image_url' => 'https://www.coca-cola.ru/images/logo.png',
                'details' => 'The Coca-Cola Company is an American historical multinational beverage corporation and manufacturer, retailer, and marketer of nonalcoholic beverage concentrates and syrups, which is headquartered in Atlanta, Georgia.',
                'status' => 'public',
                'url' => 'http://us.coca-cola.com/home/'
            ],
            [
                'id' => 3,
                'identity' => 'd3232800-ef88-45fe-8bfb-dcc993401444',
                'email' => 'navi@sparkwoo.com',
                'first_name' => 'John',
                'last_name' => 'Smybert',
                'brand_name' => 'Natus Vincere',
                'image_url' => 'http://s.navi-gaming.com/images/navi_logo_164_143.png',
                'details' => 'Natus Vincere, the Latin phrase meaning “born to win”, is an eSports Club that ranks among the leaders in the world in competitive gaming. During the first five years of its existence, Na`Vi teams have won and defended world titles in different disciplines as well as set several world records that still stand today.',
                'status' => 'project',
                'url' => 'http://navi-gaming.com/'
            ],
            [
                'id' => 4,
                'identity' => 'fe04b80e-bf71-4928-929a-681d574f12ae',
                'email' => 'empire@sparkwoo.com',
                'first_name' => 'Robert',
                'last_name' => 'Feke',
                'brand_name' => 'McDonald\'s',
                'image_url' => 'http://www.aboutmcdonalds.com/etc/designs/aboutmcdonalds/jcr:content/genericpage/genericpagecontent/sitelevelconfiguration/logoimage.img.png',
                'details' => 'McDonald\'s is the world\'s largest chain of hamburger fast food restaurants, serving around 68 million customers daily in 119 countries across 36,535 outlets.',
                'status' => 'project',
                'url' => 'http://www.aboutmcdonalds.com/mcd.html'
            ],
            [
                'id' => 5,
                'identity' => '2cc82b50-6add-408e-bfe3-fd2a64af0224',
                'email' => 'evil-genius@sparkwoo.com',
                'first_name' => 'Jeremiah',
                'last_name' => 'Theus',
                'brand_name' => 'Microsoft',
                'image_url' => 'https://c.s-microsoft.com/uk-ua/CMSImages/Win10_GA_1002_540x304_EN_US.jpg?version=a1652bc5-3fbd-ea69-ddea-b0e3b837e657',
                'details' => 'Microsoft Corporation',
                'status' => 'project',
                'url' => 'https://www.microsoft.com'
            ],
            [
                'id' => 6,
                'identity' => 'cd1d0f4a-c628-4cf1-a4fc-3c2a720993b9',
                'email' => 'virtus-pro@sparkwoo.com',
                'first_name' => 'Patience',
                'last_name' => 'Wright',
                'brand_name' => 'Manchester United F.C.',
                'image_url' => 'https://upload.wikimedia.org/wikipedia/en/thumb/7/7a/Manchester_United_FC_crest.svg/330px-Manchester_United_FC_crest.svg.png',
                'details' => 'Manchester United Football Club is a professional football club based in Old Trafford, Greater Manchester, England, that competes in the Premier League, the top flight of English football. Nicknamed "the Red Devils", the club was founded as Newton Heath LYR Football Club in 1878, changed its name to Manchester United in 1902 and moved to its current stadium, Old Trafford, in 1910.',
                'status' => 'project',
                'url' => 'http://www.manutd.com/'
            ],
        ]);
    }
}
