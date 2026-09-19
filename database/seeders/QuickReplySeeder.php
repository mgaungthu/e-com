<?php

namespace Database\Seeders;

use App\Models\QuickReply;
use Illuminate\Database\Seeder;

class QuickReplySeeder extends Seeder
{
    public function run(): void
    {
        $quickReplies = [
            [
                'title' => 'Pre-order',
                'message' => 'ကြိုမှာထားလို့ရပါတယ်။ ပို့ပေးရမဲ့ လိပ်စာ နာမည် ဖုန်းနံပတ်လေးပြောထားပေးပါ။',
                'is_active' => true,
                'sort_order' => 10,
            ],

            [
                'title' => 'How to order via chat',
                'message' => 'မှာယူလိုပါက Chat Box မှတစ်ဆင့် မှာယူမည့် ပစ္စည်းအမျိုးစား အရေတွက် နှင့် ပို့ပေးရမဲ့ လိပ်စာ နာမည် ဖုန်းနံပတ်လေးပြောပြီး မှာယူနိုင်ပါသည်။',
                'is_active' => true,
                'sort_order' => 20,
            ],

            [
                'title' => 'Delivery & payment methods',
                'message' => 'Delivery ဖြင့် မြို့တွင်း/နယ်ဝေး ပို့ဆောင်ပေးပါသည်။ ပစ္စည်းရောက်ငွေချေ ရရှိနိုင်ပါသည်။ ပစ္စည်းရောက်ငွေချေဝန်ဆောင်မှုမရရှိနိုင်သည့်မြို့များအတွက် ဂိတ်ချဖြင့်သာပို့ဆောင်ပေးလို့ရမည် ဖြစ်ပြီး ပစ္စည်းဖိုးငွေ + ဂိတ်ချခ ကြိုရှင်းဖြင့်သာမှာယူနိုင်ပါမည်။

ငွေပေးချေမှုများအတွက် Kpay, Wave, AYA Pay, UAB Pay, A Plus Pay, CTZPay, CB Pay နှင့် Bank Transfer များအတွက် KBZ Bank, AYA Bank, Yoma Bank, A Bank, UAB Bank စသည်တို့ဖြင့်ပေးချေနိုင်ပါသည်။',
                'is_active' => true,
                'sort_order' => 30,
            ],

            [
                'title' => 'Will send',
                'message' => 'ဟုတ်ကဲ့ ပို့ပေးလိုက်ပါမယ်။',
                'is_active' => true,
                'sort_order' => 40,
            ],

            [
                'title' => 'Leave order details',
                'message' => 'Reply မပြန်ဖြစ်ခဲ့သေးလျှင်လိုချင်တဲ့ ပစ္စည်းနာမည် သို့မဟုတ် ပုံဖြင့်ပြသ၍ အရေတွက်၊ ပို့ပေးရမဲ့ လိပ်စာ နာမည် ဖုန်းနံပတ် ချန်ထားပြီး အော်ဒါမှာထားနိုင်ပါသည်။',
                'is_active' => true,
                'sort_order' => 50,
            ],

            [
                'title' => 'Delivery time',
                'message' => 'Delivery ကြာချိန်

ရန်ကုန်မြို့တွင်း ၃ ရက်အတွင်း (မှာပြီးနောက်နေ့မှစ၍)

နယ်ဝေး - 4/5/6 ရက်အတွင်း (ပုံမှန်အားဖြင့်) ကြာနိုင်ပါသည်။',
                'is_active' => true,
                'sort_order' => 60,
            ],

            [
                'title' => 'Delivery phone call',
                'message' => 'Deli လာရင် ဖုန်းဆက်ပါလိမ့်မယ်။ မှာယူအားပေးတာကျေးဇူးပါ။',
                'is_active' => true,
                'sort_order' => 70,
            ],

            [
                'title' => 'Cash on delivery',
                'message' => 'ပစ္စည်းရောက်ငွေချေရပါတယ်။ မရတဲ့မြို့တွေလည်းရှိပါမယ်။',
                'is_active' => true,
                'sort_order' => 80,
            ],

            [
                'title' => 'Same-day delivery',
                'message' => 'နေ့ချင်းပို့ဆောင်မှုအတွက် Grab သို့မဟုတ် ကီလိုတက်စီဖြင့်သာပို့ဆောင်ပေးလို့ရပါမည်။',
                'is_active' => true,
                'sort_order' => 90,
            ],

            [
                'title' => 'Next-day delivery fee',
                'message' => 'အော်ဒါမှာပြီးနောက်ရက်ပို့ဆောင်ပေးရန် ဝန်ဆောင်မှုအတွက် Deli ခ ပုံမှန်နှုန်းရဲ့ ၂ ဆ ဖြစ်ပါမည်။',
                'is_active' => true,
                'sort_order' => 100,
            ],

            [
                'title' => 'Delivery cutoff time',
                'message' => 'Order Deli Cutoff Time နေ့လည် ၁ နာရီ နောက်ပိုင်းမှာတဲ့ အော်ဒါများ မနက်ဖြန် Deli Pickup နဲ့မှ ပါပါမည်။

Delivery ရန်ကုန်မြို့တွင်း မှာယူပြီးနောက်နေ့မှစ ၃ ရက်အတွင်းရောက်ပါမယ်။',
                'is_active' => true,
                'sort_order' => 110,
            ],

            [
                'title' => 'Payment info',
                'message' => 'Payment Info

09 252 883 808

Kpay, AYA Pay, Wave

Name - Zwe Ko Ko',
                'is_active' => true,
                'sort_order' => 120,
            ],
        ];

        foreach ($quickReplies as $quickReply) {
            QuickReply::updateOrCreate(
                [
                    'title' => $quickReply['title'],
                ],
                [
                    'message' => $quickReply['message'],
                    'is_active' => $quickReply['is_active'],
                    'sort_order' => $quickReply['sort_order'],
                    'created_by' => null,
                ],
            );
        }
    }
}