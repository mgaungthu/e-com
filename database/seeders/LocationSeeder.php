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
                'shipping_fee' => 6000,
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

        $yangonTownships = $this->seedLocations(
            $yangonCity,
            [
                /*
                |--------------------------------------------------------------------------
                | Yangon - 6,000 MMK
                |--------------------------------------------------------------------------
                */

                ['name_en' => 'Kyauktada', 'name_mm' => 'ကျောက်တံတား'],
                ['name_en' => 'Pabedan', 'name_mm' => 'ပန်းဘဲတန်း'],
                ['name_en' => 'Pazundaung', 'name_mm' => 'ပုဇွန်တောင်'],
                ['name_en' => 'Botahtaung', 'name_mm' => 'ဗိုလ်တထောင်'],
                ['name_en' => 'Mingala Taungnyunt', 'name_mm' => 'မင်္ဂလာတောင်ညွန့်'],
                ['name_en' => 'Latha', 'name_mm' => 'လသာ'],
                ['name_en' => 'Lanmadaw', 'name_mm' => 'လမ်းမတော်'],

                /*
                |--------------------------------------------------------------------------
                | Yangon - 6,500 MMK
                |--------------------------------------------------------------------------
                */

                ['name_en' => 'Kamayut', 'name_mm' => 'ကမာရွတ်', 'shipping_fee' => 6500],
                ['name_en' => 'Kyimyindaing', 'name_mm' => 'ကြည့်မြင်တိုင်', 'shipping_fee' => 6500],
                ['name_en' => 'Sanchaung', 'name_mm' => 'စမ်းချောင်း', 'shipping_fee' => 6500],
                ['name_en' => 'Tamwe', 'name_mm' => 'တာမွေ', 'shipping_fee' => 6500],
                ['name_en' => 'South Okkalapa', 'name_mm' => 'တောင်ဥက္ကလာပ', 'shipping_fee' => 6500],
                ['name_en' => 'North Okkalapa', 'name_mm' => 'မြောက်ဥက္ကလာပ', 'shipping_fee' => 6500],
                ['name_en' => 'Yankin', 'name_mm' => 'ရန်ကင်း', 'shipping_fee' => 6500],
                ['name_en' => 'Hlaing', 'name_mm' => 'လှိုင်', 'shipping_fee' => 6500],
                ['name_en' => 'Thingangyun', 'name_mm' => 'သင်္ဃန်းကျွန်း', 'shipping_fee' => 6500],
                ['name_en' => 'Thaketa', 'name_mm' => 'သာကေတ', 'shipping_fee' => 6500],
                ['name_en' => 'Ahlone', 'name_mm' => 'အလုံ', 'shipping_fee' => 6500],
                ['name_en' => 'Insein', 'name_mm' => 'အင်းစိန်', 'shipping_fee' => 6500],
                ['name_en' => 'Dagon', 'name_mm' => 'ဒဂုံ', 'shipping_fee' => 6500],
                ['name_en' => 'Dagon Seikkan', 'name_mm' => 'ဒဂုံမြို့သစ် (ဆိပ်ကမ်း)', 'shipping_fee' => 6500],
                ['name_en' => 'East Dagon', 'name_mm' => 'ဒဂုံမြို့သစ် (အရှေ့ပိုင်း)', 'shipping_fee' => 6500],
                ['name_en' => 'North Dagon', 'name_mm' => 'ဒဂုံမြို့သစ် (မြောက်ပိုင်း)', 'shipping_fee' => 6500],
                ['name_en' => 'South Dagon', 'name_mm' => 'ဒဂုံမြို့သစ် (တောင်ပိုင်း)', 'shipping_fee' => 6500],
                ['name_en' => 'Dawbon', 'name_mm' => 'ဒေါပုံ', 'shipping_fee' => 6500],
                ['name_en' => 'Mingaladon', 'name_mm' => 'မင်္ဂလာဒုံ', 'shipping_fee' => 6500],
                ['name_en' => 'Shwepyitha', 'name_mm' => 'ရွှေပြည်သာ', 'shipping_fee' => 6500],
                ['name_en' => 'Hlaingthaya', 'name_mm' => 'လှိုင်သာယာ', 'shipping_fee' => 6500],
                ['name_en' => 'Thanlyin', 'name_mm' => 'သန်လျင်', 'shipping_fee' => 6500],
                ['name_en' => 'Bahan', 'name_mm' => 'ဗဟန်း', 'shipping_fee' => 6500],
                ['name_en' => 'Mayangone', 'name_mm' => 'မရမ်းကုန်း', 'shipping_fee' => 6500],
                ['name_en' => 'Shwe Pauk Kan', 'name_mm' => 'ရွှေပေါက်ကံ', 'shipping_fee' => 6500],
                ['name_en' => 'Thuwunna', 'name_mm' => 'သုဝဏ္ဏ', 'shipping_fee' => 6500],

                /*
                |--------------------------------------------------------------------------
                | Yangon - 7,700 MMK
                |--------------------------------------------------------------------------
                */

                ['name_en' => 'Kyauktan', 'name_mm' => 'ကျောက်တန်း', 'shipping_fee' => 7700],
                ['name_en' => 'Kawhmu', 'name_mm' => 'ကော့မှူး', 'shipping_fee' => 7700],
                ['name_en' => 'Kungyangon', 'name_mm' => 'ကွမ်းခြံကုန်း', 'shipping_fee' => 7700],
                ['name_en' => 'Okkan', 'name_mm' => 'ဥက္ကံ', 'shipping_fee' => 7700],
                ['name_en' => 'Khayan', 'name_mm' => 'ခရမ်း', 'shipping_fee' => 7700],
                ['name_en' => 'Taikkyi', 'name_mm' => 'တိုက်ကြီး', 'shipping_fee' => 7700],
                ['name_en' => 'Twante', 'name_mm' => 'တွံတေး', 'shipping_fee' => 7700],
                ['name_en' => 'Dala', 'name_mm' => 'ဒလ', 'shipping_fee' => 7700],
                ['name_en' => 'Hmawbi', 'name_mm' => 'မှော်ဘီ', 'shipping_fee' => 7700],
                ['name_en' => 'Htauk Kyant', 'name_mm' => 'ထောက်ကြန့်', 'shipping_fee' => 7700],
                ['name_en' => 'Hlegu', 'name_mm' => 'လှည်းကူး', 'shipping_fee' => 7700],
                ['name_en' => 'Thongwa', 'name_mm' => 'သုံးခွ', 'shipping_fee' => 7700],
                ['name_en' => 'Htantabin', 'name_mm' => 'ထန်းတပင်', 'shipping_fee' => 7700],
                ['name_en' => 'Seikgyi Kanaungto', 'name_mm' => 'ဆိပ်ကြီးခနောင်တို', 'shipping_fee' => 7700],
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | Existing Ward Data
        |--------------------------------------------------------------------------
        */

        $southOkkalapa = $yangonTownships['South Okkalapa'];

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

                // Existing explicit override preserved.
                'shipping_fee' => 2000,

                'is_active' => true,
                'sort_order' => 2,
            ],
        );

        $northOkkalapa = $yangonTownships['North Okkalapa'];

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
                'shipping_fee' => 6000,
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

        $this->seedLocations(
            $mandalayCity,
            [
                ['name_en' => 'Chanayethazan', 'name_mm' => 'ချမ်းအေးသာစံ'],
                ['name_en' => 'Chanmyathazi', 'name_mm' => 'ချမ်းမြသာစည်'],
                ['name_en' => 'Pyigyitagon', 'name_mm' => 'ပြည်ကြီးတံခွန်'],
                ['name_en' => 'Mahaaungmyay', 'name_mm' => 'မဟာအောင်မြေ'],
                ['name_en' => 'Aungmyaythazan', 'name_mm' => 'အောင်မြေသာစံ'],
                ['name_en' => 'Amarapura', 'name_mm' => 'အမရပူရ'],
                ['name_en' => 'Kyaukpadaung', 'name_mm' => 'ကျောက်ပန်းတောင်း'],
                ['name_en' => 'Kyaukse', 'name_mm' => 'ကျောက်ဆည်'],
                ['name_en' => 'Kume', 'name_mm' => 'ကူမဲ'],
                ['name_en' => 'Sintgaing', 'name_mm' => 'စဉ့်ကိုင်'],
                ['name_en' => 'Singu', 'name_mm' => 'စဉ့်ကူး'],
                ['name_en' => 'Zayatkwin', 'name_mm' => 'ဇရပ်ကွင်း'],
                ['name_en' => 'Nyaung-U', 'name_mm' => 'ညောင်ဦး'],
                ['name_en' => 'Tada-U', 'name_mm' => 'တံတားဦး'],
                ['name_en' => 'Taungtha', 'name_mm' => 'တောင်သာ'],
                ['name_en' => 'Natogyi', 'name_mm' => 'နွားထိုးကြီး'],
                ['name_en' => 'Pyin Oo Lwin', 'name_mm' => 'ပြင်ဦးလွင်'],
                ['name_en' => 'Paleik', 'name_mm' => 'ပုလိပ်'],
                ['name_en' => 'Pyawbwe', 'name_mm' => 'ပျော်ဘွယ်'],
                ['name_en' => 'Bagan', 'name_mm' => 'ပုဂံ'],
                ['name_en' => 'Patheingyi', 'name_mm' => 'ပုသိမ်ကြီး'],
                ['name_en' => 'Meiktila', 'name_mm' => 'မိတ္ထီလာ'],
                ['name_en' => 'Mogok', 'name_mm' => 'မိုးကုတ်'],
                ['name_en' => 'Myingyan', 'name_mm' => 'မြင်းခြံ'],
                ['name_en' => 'Myitnge', 'name_mm' => 'မြစ်ငယ်'],
                ['name_en' => 'Mahlaing', 'name_mm' => 'မလှိုင်'],
                ['name_en' => 'Myittha', 'name_mm' => 'မြစ်သား'],
                ['name_en' => 'Madaya', 'name_mm' => 'မတ္တရာ'],
                ['name_en' => 'Yamethin', 'name_mm' => 'ရမည်းသင်း'],
                ['name_en' => 'Letpanhla', 'name_mm' => 'လက်ပံလှ'],
                ['name_en' => 'Wundwin', 'name_mm' => 'ဝမ်းတွင်း'],
                ['name_en' => 'Thazi', 'name_mm' => 'သာစည်'],
                ['name_en' => 'Thae Taw', 'name_mm' => 'သဲတော'],
                ['name_en' => 'Han Myint Mo Road Junction', 'name_mm' => 'ဟန်မြင့်မိုရ်လမ်းခွဲ'],
                ['name_en' => 'Ohn Chaw', 'name_mm' => 'အုန်းချော'],
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
                'shipping_fee' => 6000,
                'is_active' => true,
                'sort_order' => 3,
            ],
        );

        $this->seedLocations(
            $naypyidaw,
            [
                ['name_en' => 'Ottarathiri', 'name_mm' => 'ဥတ္တရသီရိ'],
                ['name_en' => 'Pobbathiri', 'name_mm' => 'ပုဗ္ဗသီရိ'],
                ['name_en' => 'Dekkhinathiri', 'name_mm' => 'ဒက္ခိဏသီရိ'],
                ['name_en' => 'Zabuthiri', 'name_mm' => 'ဇမ္ဗူသီရိ'],
                ['name_en' => 'Zeyathiri', 'name_mm' => 'ဇေယျာသီရိ'],
                ['name_en' => 'Nyaunglun', 'name_mm' => 'ညောင်လွန့်'],
                ['name_en' => 'Tatkon', 'name_mm' => 'တပ်ကုန်း'],
                ['name_en' => 'Pyinmana', 'name_mm' => 'ပျဉ်းမနား'],
                ['name_en' => 'Pyankabye', 'name_mm' => 'ပြန်ကပြေး'],
                ['name_en' => 'Lewe', 'name_mm' => 'လယ်ဝေး'],
                ['name_en' => 'Thawutti', 'name_mm' => 'သာဝတ္ထိ'],
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | Kayin State
        |--------------------------------------------------------------------------
        |
        | Current application uses "region" for top-level location containers.
        | Keep that convention for states too.
        |
        */

        $kayinState = Location::query()->updateOrCreate(
            [
                'parent_id' => null,
                'name_en' => 'Kayin State',
                'type' => 'region',
            ],
            [
                'name_mm' => 'ကရင်ပြည်နယ်',
                'shipping_fee' => 6000,
                'is_active' => true,
                'sort_order' => 4,
            ],
        );

        $this->seedLocations(
            $kayinState,
            [
                ['name_en' => 'Hpa-An', 'name_mm' => 'ဘားအံ'],
                ['name_en' => 'Payathonzu', 'name_mm' => 'ဘုရားသုံးဆူ'],
                ['name_en' => 'Myaing Kalay', 'name_mm' => 'မြိုင်ကလေး'],
                ['name_en' => 'Hlaingbwe', 'name_mm' => 'လှိုင်းဘွဲ့'],
                ['name_en' => 'Eindu', 'name_mm' => 'အိန္ဒု'],

                ['name_en' => 'Myawaddy', 'name_mm' => 'မြဝတီ', 'shipping_fee' => 8500],
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | Bago Region
        |--------------------------------------------------------------------------
        */

        $bagoRegion = Location::query()->updateOrCreate(
            [
                'parent_id' => null,
                'name_en' => 'Bago Region',
                'type' => 'region',
            ],
            [
                'name_mm' => 'ပဲခူးတိုင်းဒေသကြီး',
                'shipping_fee' => 6000,
                'is_active' => true,
                'sort_order' => 5,
            ],
        );

        $this->seedLocations(
            $bagoRegion,
            [
                ['name_en' => 'Gyobingauk', 'name_mm' => 'ကြို့ပင်ကောက်'],
                ['name_en' => 'Ketumati Myothit', 'name_mm' => 'ကေတုမတီမြို့သစ်'],
                ['name_en' => 'Kyauktaga', 'name_mm' => 'ကျောက်တံခါး'],
                ['name_en' => 'Swa', 'name_mm' => 'ဆွာ'],
                ['name_en' => 'Zigon', 'name_mm' => 'ဇီးကုန်း'],
                ['name_en' => 'Zeyawadi', 'name_mm' => 'ဇေယျဝတီ'],
                ['name_en' => 'Nyaunglebin', 'name_mm' => 'ညောင်လေးပင်'],
                ['name_en' => 'Nyaung Che Htauk', 'name_mm' => 'ညောင်ခြေထောက်'],
                ['name_en' => 'Okshitpin', 'name_mm' => 'ဥသျှစ်ပင်'],
                ['name_en' => 'Taungoo', 'name_mm' => 'တောင်ငူ'],
                ['name_en' => 'Taw Kywe Inn', 'name_mm' => 'တောကျွဲအင်း'],
                ['name_en' => 'Daik-U', 'name_mm' => 'ဒိုက်ဦး'],
                ['name_en' => 'Nattalin', 'name_mm' => 'နတ်တလင်း'],
                ['name_en' => 'Bago', 'name_mm' => 'ပဲခူး'],
                ['name_en' => 'Pyay', 'name_mm' => 'ပြည်'],
                ['name_en' => 'Paungde', 'name_mm' => 'ပေါင်းတည်'],
                ['name_en' => 'Puteekone', 'name_mm' => 'ပုတီးကုန်း'],
                ['name_en' => 'Penwegon', 'name_mm' => 'ပဲနွယ်ကုန်း'],
                ['name_en' => 'Pyuntaza', 'name_mm' => 'ပြွန်တန်ဆာ'],
                ['name_en' => 'Paukkhaung', 'name_mm' => 'ပေါက်ခေါင်း'],
                ['name_en' => 'Phyu', 'name_mm' => 'ဖြူး'],
                ['name_en' => 'Shwedaung', 'name_mm' => 'ရွှေတောင်'],
                ['name_en' => 'Yedashe', 'name_mm' => 'ရေတာရှည်'],
                ['name_en' => 'Shwegyin', 'name_mm' => 'ရွှေကျင်'],
                ['name_en' => 'Letpadan', 'name_mm' => 'လက်ပံတန်း'],
                ['name_en' => 'Waw', 'name_mm' => 'ဝေါ'],
                ['name_en' => 'Set Taing Kone', 'name_mm' => 'စက်တိုင်ကုန်း'],
                ['name_en' => 'Thegon', 'name_mm' => 'သဲကုန်း'],
                ['name_en' => 'Thanatpin', 'name_mm' => 'သနပ်ပင်'],
                ['name_en' => 'Thagara', 'name_mm' => 'သာဂရ'],
                ['name_en' => 'Thonse', 'name_mm' => 'သုံးဆယ်'],
                ['name_en' => 'Thayarwady', 'name_mm' => 'သာယာဝတီ'],
                ['name_en' => 'Thetkala', 'name_mm' => 'သက္ကလ'],
                ['name_en' => 'Inma', 'name_mm' => 'အင်းမ'],
                ['name_en' => 'Intagaw', 'name_mm' => 'အင်းတကော်'],
                ['name_en' => 'Okpho', 'name_mm' => 'အုတ်ဖို'],
                ['name_en' => 'Oktwin', 'name_mm' => 'အုတ်တွင်း'],
                ['name_en' => 'Lower Minhla', 'name_mm' => 'အောက်မင်းလှ'],
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | Tanintharyi Region
        |--------------------------------------------------------------------------
        */

        $tanintharyiRegion = Location::query()->updateOrCreate(
            [
                'parent_id' => null,
                'name_en' => 'Tanintharyi Region',
                'type' => 'region',
            ],
            [
                'name_mm' => 'တနင်္သာရီတိုင်းဒေသကြီး',
                'shipping_fee' => 9500,
                'is_active' => true,
                'sort_order' => 6,
            ],
        );

        $this->seedLocations(
            $tanintharyiRegion,
            [
                ['name_en' => 'Kawthaung', 'name_mm' => 'ကော့သောင်း'],
                ['name_en' => 'Dawei', 'name_mm' => 'ထားဝယ်'],
                ['name_en' => 'Myeik', 'name_mm' => 'မြိတ်'],
                ['name_en' => 'Maungmagan', 'name_mm' => 'မောင်းမကန်'],
                ['name_en' => 'Yebyu', 'name_mm' => 'ရေဖြူ'],
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | Ayeyarwady Region
        |--------------------------------------------------------------------------
        */

        $ayeyarwadyRegion = Location::query()->updateOrCreate(
            [
                'parent_id' => null,
                'name_en' => 'Ayeyarwady Region',
                'type' => 'region',
            ],
            [
                'name_mm' => 'ဧရာဝတီတိုင်းဒေသကြီး',
                'shipping_fee' => 6000,
                'is_active' => true,
                'sort_order' => 7,
            ],
        );

        $this->seedLocations(
            $ayeyarwadyRegion,
            [
                ['name_en' => 'Kyonpyaw', 'name_mm' => 'ကျုံပျော်'],
                ['name_en' => 'Kyaiklat', 'name_mm' => 'ကျိုက်လတ်'],
                ['name_en' => 'Kyaunggon', 'name_mm' => 'ကျောင်းကုန်း'],
                ['name_en' => 'Kyangin', 'name_mm' => 'ကြံခင်း'],
                ['name_en' => 'Chaungtha', 'name_mm' => 'ချောင်းသာ'],
                ['name_en' => 'Ngathaingchaung', 'name_mm' => 'ငါးသိုင်းချောင်း'],
                ['name_en' => 'Dedaye', 'name_mm' => 'ဒေးဒရဲ'],
                ['name_en' => 'Zalun', 'name_mm' => 'ဇလွန်'],
                ['name_en' => 'Nyaungdon', 'name_mm' => 'ညောင်တုန်း'],
                ['name_en' => 'Danubyu', 'name_mm' => 'ဓနုဖြူ'],
                ['name_en' => 'Pathein', 'name_mm' => 'ပုသိမ်'],
                ['name_en' => 'Pantanaw', 'name_mm' => 'ပန်းတနော်'],
                ['name_en' => 'Pyapon', 'name_mm' => 'ဖျာပုံ'],
                ['name_en' => 'Bogale', 'name_mm' => 'ဘိုကလေး'],
                ['name_en' => 'Maubin', 'name_mm' => 'မအူပင်'],
                ['name_en' => 'Myaungmya', 'name_mm' => 'မြောင်းမြ'],
                ['name_en' => 'Myanaung', 'name_mm' => 'မြန်အောင်'],
                ['name_en' => 'Mawlamyinegyun', 'name_mm' => 'မော်ကျွန်း'],
                ['name_en' => 'Yekyi', 'name_mm' => 'ရေကြည်'],
                ['name_en' => 'Labutta', 'name_mm' => 'လပွတ္တာ'],
                ['name_en' => 'Wakema', 'name_mm' => 'ဝါးခယ်မ'],
                ['name_en' => 'Hinthada', 'name_mm' => 'ဟင်္သာတ'],
                ['name_en' => 'Einme', 'name_mm' => 'အိမ်မဲ'],
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | Sagaing Region
        |--------------------------------------------------------------------------
        */

        $sagaingRegion = Location::query()->updateOrCreate(
            [
                'parent_id' => null,
                'name_en' => 'Sagaing Region',
                'type' => 'region',
            ],
            [
                'name_mm' => 'စစ်ကိုင်းတိုင်းဒေသကြီး',
                'shipping_fee' => 6000,
                'is_active' => true,
                'sort_order' => 8,
            ],
        );

        $this->seedLocations(
            $sagaingRegion,
            [
                ['name_en' => 'Kanbalu', 'name_mm' => 'ကန့်ဘလူ'],
                ['name_en' => 'Kyunhla', 'name_mm' => 'ကျွန်းလှ'],
                ['name_en' => 'Monywa', 'name_mm' => 'မုံရွာ'],
                ['name_en' => 'Shwebo', 'name_mm' => 'ရွှေဘို'],
                ['name_en' => 'Indaw', 'name_mm' => 'အင်းတော်'],
                ['name_en' => 'Sagaing', 'name_mm' => 'စစ်ကိုင်း'],

                ['name_en' => 'Katha', 'name_mm' => 'ကသာ', 'shipping_fee' => 8500],
                ['name_en' => 'Htigyaing', 'name_mm' => 'ထီးချိုင့်', 'shipping_fee' => 8500],
                ['name_en' => 'Kawlin', 'name_mm' => 'ကောလင်း', 'shipping_fee' => 8500],

                ['name_en' => 'Tamu', 'name_mm' => 'တမူး', 'shipping_fee' => 9500],
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | Magway Region
        |--------------------------------------------------------------------------
        */

        $magwayRegion = Location::query()->updateOrCreate(
            [
                'parent_id' => null,
                'name_en' => 'Magway Region',
                'type' => 'region',
            ],
            [
                'name_mm' => 'မကွေးတိုင်းဒေသကြီး',
                'shipping_fee' => 6000,
                'is_active' => true,
                'sort_order' => 9,
            ],
        );

        $this->seedLocations(
            $magwayRegion,
            [
                ['name_en' => 'Kamma', 'name_mm' => 'ကမ္မ'],
                ['name_en' => 'Chauk', 'name_mm' => 'ချောက်'],
                ['name_en' => 'Sagu', 'name_mm' => 'စကု'],
                ['name_en' => 'Salay', 'name_mm' => 'စလေ'],
                ['name_en' => 'Salin', 'name_mm' => 'စလင်း'],
                ['name_en' => 'Seikphyu', 'name_mm' => 'ဆိပ်ဖြူ'],
                ['name_en' => 'Sinbyukyun', 'name_mm' => 'ဆင်ဖြူကျွန်း'],
                ['name_en' => 'Satthwa', 'name_mm' => 'ဆတ်သွား'],
                ['name_en' => 'Taungdwingyi', 'name_mm' => 'တောင်တွင်းကြီး'],
                ['name_en' => 'Natmauk', 'name_mm' => 'နတ်မောက်'],
                ['name_en' => 'Pakokku', 'name_mm' => 'ပခုက္ကူ'],
                ['name_en' => 'Pwintbyu', 'name_mm' => 'ပွင့်ဖြူ'],
                ['name_en' => 'Magway', 'name_mm' => 'မကွေး'],
                ['name_en' => 'Minbu', 'name_mm' => 'မင်းဘူး'],
                ['name_en' => 'Myothit', 'name_mm' => 'မြို့သစ်မြို့'],
                ['name_en' => 'Yenangyaung', 'name_mm' => 'ရေနံချောင်း'],
                ['name_en' => 'Yesagyo', 'name_mm' => 'ရေစကြို'],
                ['name_en' => 'Lay Kaing', 'name_mm' => 'လယ်ကိုင်း'],
                ['name_en' => 'Thayet', 'name_mm' => 'သရက်'],
                ['name_en' => 'Thit Yar Kauk', 'name_mm' => 'သစ်ရာကောက်'],
                ['name_en' => 'Thit Nyi Kone', 'name_mm' => 'သစ်ညီကုန်း'],
                ['name_en' => 'Upper Minhla', 'name_mm' => 'အထက်မင်းလှ'],
                ['name_en' => 'Aunglan', 'name_mm' => 'အောင်လံ'],
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | Chin State
        |--------------------------------------------------------------------------
        */

        $chinState = Location::query()->updateOrCreate(
            [
                'parent_id' => null,
                'name_en' => 'Chin State',
                'type' => 'region',
            ],
            [
                'name_mm' => 'ချင်းပြည်နယ်',
                'shipping_fee' => 12000,
                'is_active' => true,
                'sort_order' => 10,
            ],
        );

        $this->seedLocations(
            $chinState,
            [
                ['name_en' => 'Hakha', 'name_mm' => 'ဟားခါး'],
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | Shan State
        |--------------------------------------------------------------------------
        */

        $shanState = Location::query()->updateOrCreate(
            [
                'parent_id' => null,
                'name_en' => 'Shan State',
                'type' => 'region',
            ],
            [
                'name_mm' => 'ရှမ်းပြည်နယ်',
                'shipping_fee' => 6000,
                'is_active' => true,
                'sort_order' => 11,
            ],
        );

        $this->seedLocations(
            $shanState,
            [
                /*
                |--------------------------------------------------------------------------
                | Shan - 6,000 MMK
                |--------------------------------------------------------------------------
                */

                ['name_en' => 'Kalaw', 'name_mm' => 'ကလော'],
                ['name_en' => 'Kyaukme', 'name_mm' => 'ကျောက်မဲ'],
                ['name_en' => 'Hsihseng', 'name_mm' => 'ဆီဆိုင်'],
                ['name_en' => 'Nyaungshwe', 'name_mm' => 'ညောင်ရွှေ'],
                ['name_en' => 'Taunggyi', 'name_mm' => 'တောင်ကြီး'],
                ['name_en' => 'Naungcho', 'name_mm' => 'နောင်ချို'],
                ['name_en' => 'Pindaya', 'name_mm' => 'ပင်းတယ'],
                ['name_en' => 'Baw Htoo', 'name_mm' => 'ဗထူး'],
                ['name_en' => 'Shwenyaung', 'name_mm' => 'ရွှေညောင်'],
                ['name_en' => 'Yatsauk', 'name_mm' => 'ရပ်စောက်'],
                ['name_en' => 'Hsipaw', 'name_mm' => 'သီပေါ'],
                ['name_en' => 'Heho', 'name_mm' => 'ဟဲဟိုး'],
                ['name_en' => 'Aye Thar Yar', 'name_mm' => 'အေးသာယာ'],
                ['name_en' => 'Aungban', 'name_mm' => 'အောင်ပန်း'],

                /*
                |--------------------------------------------------------------------------
                | Shan - 8,500 MMK
                |--------------------------------------------------------------------------
                */

                ['name_en' => 'Pinlon', 'name_mm' => 'ပင်လုံ', 'shipping_fee' => 8500],
                ['name_en' => 'Momeik', 'name_mm' => 'မိုးမိတ်', 'shipping_fee' => 8500],
                ['name_en' => 'Lashio', 'name_mm' => 'လားရှိုး', 'shipping_fee' => 8500],
                ['name_en' => 'Langkho', 'name_mm' => 'လင်းခေး', 'shipping_fee' => 8500],
                ['name_en' => 'Loilen', 'name_mm' => 'လွိုင်လင်', 'shipping_fee' => 8500],

                /*
                |--------------------------------------------------------------------------
                | Shan - 9,500 MMK
                |--------------------------------------------------------------------------
                */

                ['name_en' => 'Muse', 'name_mm' => 'မူဆယ်', 'shipping_fee' => 9500],
                ['name_en' => 'Mong Nai', 'name_mm' => 'မိုးနဲ', 'shipping_fee' => 9500],
                ['name_en' => 'Nansang (South)', 'name_mm' => 'နမ့်စမ် (တောင်)', 'shipping_fee' => 9500],

                /*
                |--------------------------------------------------------------------------
                | Shan - 12,000 MMK
                |--------------------------------------------------------------------------
                */

                ['name_en' => 'Tachileik', 'name_mm' => 'တာချီလိတ်', 'shipping_fee' => 12000],
                ['name_en' => 'Mong Hsat', 'name_mm' => 'မိုင်းဆတ်', 'shipping_fee' => 12000],
                ['name_en' => 'Kengtung', 'name_mm' => 'ကျိုင်းတုံ', 'shipping_fee' => 12000],
                ['name_en' => 'Laukkai', 'name_mm' => 'လောက်ကိုင်', 'shipping_fee' => 12000],
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | Mon State
        |--------------------------------------------------------------------------
        */

        $monState = Location::query()->updateOrCreate(
            [
                'parent_id' => null,
                'name_en' => 'Mon State',
                'type' => 'region',
            ],
            [
                'name_mm' => 'မွန်ပြည်နယ်',
                'shipping_fee' => 6000,
                'is_active' => true,
                'sort_order' => 12,
            ],
        );

        $this->seedLocations(
            $monState,
            [
                ['name_en' => 'Kyaikto', 'name_mm' => 'ကျိုက်ထို'],
                ['name_en' => 'Kyaikmaraw', 'name_mm' => 'ကျိုက်မရော'],
                ['name_en' => 'Kyaikkhami', 'name_mm' => 'ကျိုက္ခမီ'],
                ['name_en' => 'Chaungzon', 'name_mm' => 'ချောင်းဆုံ'],
                ['name_en' => 'Zin Kyaik', 'name_mm' => 'ဇင်းကျိုက်'],
                ['name_en' => 'Paung', 'name_mm' => 'ပေါင်'],
                ['name_en' => 'Bilin', 'name_mm' => 'ဘီးလင်း'],
                ['name_en' => 'Mawlamyine', 'name_mm' => 'မော်လမြိုင်'],
                ['name_en' => 'Mottama', 'name_mm' => 'မုတ္တမ'],
                ['name_en' => 'Mudon', 'name_mm' => 'မုဒုံ'],
                ['name_en' => 'Ye', 'name_mm' => 'ရေး'],
                ['name_en' => 'Lamine', 'name_mm' => 'လမိုင်း'],
                ['name_en' => 'Thanbyuzayat', 'name_mm' => 'သံဖြူဇရပ်'],
                ['name_en' => 'Thaton', 'name_mm' => 'သထုံ'],
                ['name_en' => 'Suvannawadi', 'name_mm' => 'သုဝဏ္ဏဝတီ'],
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | Rakhine State
        |--------------------------------------------------------------------------
        */

        $rakhineState = Location::query()->updateOrCreate(
            [
                'parent_id' => null,
                'name_en' => 'Rakhine State',
                'type' => 'region',
            ],
            [
                'name_mm' => 'ရခိုင်ပြည်နယ်',
                'shipping_fee' => 9500,
                'is_active' => true,
                'sort_order' => 13,
            ],
        );

        $this->seedLocations(
            $rakhineState,
            [
                ['name_en' => 'Kyaukphyu', 'name_mm' => 'ကျောက်ဖြူ'],
                ['name_en' => 'Kyauktaw', 'name_mm' => 'ကျောက်တော်'],
                ['name_en' => 'Gwa', 'name_mm' => 'ဂွ'],
                ['name_en' => 'Ngapali', 'name_mm' => 'ငပလီ'],
                ['name_en' => 'Sittwe', 'name_mm' => 'စစ်တွေ'],
                ['name_en' => 'Toungup', 'name_mm' => 'တောင်ကုတ်'],
                ['name_en' => 'Ponnagyun', 'name_mm' => 'ပုဏ္ဏားကျွန်း'],
                ['name_en' => 'Pauktaw', 'name_mm' => 'ပေါက်တော'],
                ['name_en' => 'Buthidaung', 'name_mm' => 'ဘူးသီးတောင်'],
                ['name_en' => 'Minbya', 'name_mm' => 'မင်းပြား'],
                ['name_en' => 'Mrauk-U', 'name_mm' => 'မြောက်ဦး'],
                ['name_en' => 'Maungdaw', 'name_mm' => 'မောင်းတော'],
                ['name_en' => 'Ramree', 'name_mm' => 'ရမ်းဗြဲ'],
                ['name_en' => 'Thandwe', 'name_mm' => 'သံတွဲ'],
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | Kachin State
        |--------------------------------------------------------------------------
        */

        $kachinState = Location::query()->updateOrCreate(
            [
                'parent_id' => null,
                'name_en' => 'Kachin State',
                'type' => 'region',
            ],
            [
                'name_mm' => 'ကချင်ပြည်နယ်',
                'shipping_fee' => 9500,
                'is_active' => true,
                'sort_order' => 14,
            ],
        );

        $this->seedLocations(
            $kachinState,
            [
                /*
                |--------------------------------------------------------------------------
                | Kachin - 9,500 MMK
                |--------------------------------------------------------------------------
                */

                ['name_en' => 'Sinkhan', 'name_mm' => 'စင်းခန်း'],
                ['name_en' => 'Nammati', 'name_mm' => 'နမ္မတီး'],
                ['name_en' => 'Bhamo', 'name_mm' => 'ဗန်းမော်'],
                ['name_en' => 'Myitkyina', 'name_mm' => 'မြစ်ကြီးနား'],
                ['name_en' => 'Mogaung', 'name_mm' => 'မိုးကောင်း'],
                ['name_en' => 'Mohnyin', 'name_mm' => 'မိုးညှင်း'],
                ['name_en' => 'Shwegu', 'name_mm' => 'ရွှေကူ'],
                ['name_en' => 'Lweje', 'name_mm' => 'လွယ်ဂျယ်'],
                ['name_en' => 'Waingmaw', 'name_mm' => 'ဝိုင်းမော်'],
                ['name_en' => 'Hopin', 'name_mm' => 'ဟိုပင်'],
                ['name_en' => 'Indawgyi', 'name_mm' => 'အင်းတော်ကြီး'],

                /*
                |--------------------------------------------------------------------------
                | Kachin - 12,000 MMK
                |--------------------------------------------------------------------------
                */

                ['name_en' => 'Tanai', 'name_mm' => 'တနိုင်း', 'shipping_fee' => 12000],
                ['name_en' => 'Hpakant', 'name_mm' => 'ဖားကန့်', 'shipping_fee' => 12000],
                ['name_en' => 'Mansi', 'name_mm' => 'မန်စီ', 'shipping_fee' => 12000],
                ['name_en' => 'Lonkin', 'name_mm' => 'လုံးခင်း', 'shipping_fee' => 12000],
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | Kayah State
        |--------------------------------------------------------------------------
        */

        $kayahState = Location::query()->updateOrCreate(
            [
                'parent_id' => null,
                'name_en' => 'Kayah State',
                'type' => 'region',
            ],
            [
                'name_mm' => 'ကယားပြည်နယ်',
                'shipping_fee' => 12000,
                'is_active' => true,
                'sort_order' => 15,
            ],
        );

        $this->seedLocations(
            $kayahState,
            [
                ['name_en' => 'Loikaw', 'name_mm' => 'လွိုင်ကော်'],
            ],
        );
    }

    /**
     * Seed child delivery locations while keeping the existing
     * updateOrCreate pattern and shipping-fee inheritance behavior.
     *
     * When shipping_fee is omitted, the location inherits the fee
     * from its parent according to the existing application rules.
     *
     * @param  array<int, array{
     *     name_en: string,
     *     name_mm: string,
     *     shipping_fee?: int|float|null,
     *     type?: string
     * }>  $locations
     * @return array<string, Location>
     */
    private function seedLocations(
        Location $parent,
        array $locations,
    ): array {
        $seededLocations = [];

        foreach ($locations as $index => $location) {
            $type = $location['type'] ?? 'township';

            $seeded = Location::query()->updateOrCreate(
                [
                    'parent_id' => $parent->id,
                    'name_en' => $location['name_en'],
                    'type' => $type,
                ],
                [
                    'name_mm' => $location['name_mm'],
                    'shipping_fee' => $location['shipping_fee'] ?? null,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );

            $seededLocations[$location['name_en']] = $seeded;
        }

        return $seededLocations;
    }
}