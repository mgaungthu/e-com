<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Yangon Region
        |--------------------------------------------------------------------------
        */

        $yangonRegion = Location::query()->updateOrCreate(
            [
                'parent_id' => null,
                'name_en' => 'Yangon Region',
                'type' => 'region',
            ],
            [
                'name_mm' => 'ရန်ကုန်တိုင်းဒေသကြီး',
                'shipping_fee' => 3000,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        $yangonCity = Location::query()->updateOrCreate(
            [
                'parent_id' => $yangonRegion->id,
                'name_en' => 'Yangon',
                'type' => 'city',
            ],
            [
                'name_mm' => 'ရန်ကုန်',
                'shipping_fee' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | South Okkalapa
        |--------------------------------------------------------------------------
        */

        $southOkkalapa = Location::query()->updateOrCreate(
            [
                'parent_id' => $yangonCity->id,
                'name_en' => 'South Okkalapa',
                'type' => 'township',
            ],
            [
                'name_mm' => 'တောင်ဥက္ကလာပ',
                'shipping_fee' => 2500,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        Location::query()->updateOrCreate(
            [
                'parent_id' => $southOkkalapa->id,
                'name_en' => 'Ward 1',
                'type' => 'ward',
            ],
            [
                'name_mm' => 'အမှတ် (၁) ရပ်ကွက်',
                'shipping_fee' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        Location::query()->updateOrCreate(
            [
                'parent_id' => $southOkkalapa->id,
                'name_en' => 'Ward 4',
                'type' => 'ward',
            ],
            [
                'name_mm' => 'အမှတ် (၄) ရပ်ကွက်',
                'shipping_fee' => 2000,
                'is_active' => true,
                'sort_order' => 2,
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | North Okkalapa
        |--------------------------------------------------------------------------
        */

        $northOkkalapa = Location::query()->updateOrCreate(
            [
                'parent_id' => $yangonCity->id,
                'name_en' => 'North Okkalapa',
                'type' => 'township',
            ],
            [
                'name_mm' => 'မြောက်ဥက္ကလာပ',
                'shipping_fee' => 2500,
                'is_active' => true,
                'sort_order' => 2,
            ],
        );

        Location::query()->updateOrCreate(
            [
                'parent_id' => $northOkkalapa->id,
                'name_en' => 'Ward 1',
                'type' => 'ward',
            ],
            [
                'name_mm' => 'အမှတ် (၁) ရပ်ကွက်',
                'shipping_fee' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | Thingangyun
        |--------------------------------------------------------------------------
        */

        Location::query()->updateOrCreate(
            [
                'parent_id' => $yangonCity->id,
                'name_en' => 'Thingangyun',
                'type' => 'township',
            ],
            [
                'name_mm' => 'သင်္ဃန်းကျွန်း',
                'shipping_fee' => 3000,
                'is_active' => true,
                'sort_order' => 3,
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | Mandalay Region
        |--------------------------------------------------------------------------
        */

        $mandalayRegion = Location::query()->updateOrCreate(
            [
                'parent_id' => null,
                'name_en' => 'Mandalay Region',
                'type' => 'region',
            ],
            [
                'name_mm' => 'မန္တလေးတိုင်းဒေသကြီး',
                'shipping_fee' => 4500,
                'is_active' => true,
                'sort_order' => 2,
            ],
        );

        $mandalayCity = Location::query()->updateOrCreate(
            [
                'parent_id' => $mandalayRegion->id,
                'name_en' => 'Mandalay',
                'type' => 'city',
            ],
            [
                'name_mm' => 'မန္တလေး',
                'shipping_fee' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        Location::query()->updateOrCreate(
            [
                'parent_id' => $mandalayCity->id,
                'name_en' => 'Chanayethazan',
                'type' => 'township',
            ],
            [
                'name_mm' => 'ချမ်းအေးသာစံ',
                'shipping_fee' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        Location::query()->updateOrCreate(
            [
                'parent_id' => $mandalayCity->id,
                'name_en' => 'Aungmyaythazan',
                'type' => 'township',
            ],
            [
                'name_mm' => 'အောင်မြေသာစံ',
                'shipping_fee' => null,
                'is_active' => true,
                'sort_order' => 2,
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | Naypyidaw
        |--------------------------------------------------------------------------
        */

        $naypyidaw = Location::query()->updateOrCreate(
            [
                'parent_id' => null,
                'name_en' => 'Naypyidaw',
                'type' => 'region',
            ],
            [
                'name_mm' => 'နေပြည်တော်',
                'shipping_fee' => 4000,
                'is_active' => true,
                'sort_order' => 3,
            ],
        );

        Location::query()->updateOrCreate(
            [
                'parent_id' => $naypyidaw->id,
                'name_en' => 'Zabuthiri',
                'type' => 'township',
            ],
            [
                'name_mm' => 'ဇမ္ဗူသီရိ',
                'shipping_fee' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );
    }
}