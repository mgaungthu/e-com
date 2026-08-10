<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'KBZPay',
                'code' => 'kbzpay',
                'account_name' => 'Your Account Name',
                'account_number' => '09xxxxxxxxx',
                'qr_image_path' => null,
                'instructions' => 'Transfer the exact order total and upload the payment screenshot.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'AYA Pay',
                'code' => 'ayapay',
                'account_name' => 'Your Account Name',
                'account_number' => '09xxxxxxxxx',
                'qr_image_path' => null,
                'instructions' => 'Transfer the exact order total and upload the payment screenshot.',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'WavePay',
                'code' => 'wavepay',
                'account_name' => 'Your Account Name',
                'account_number' => '09xxxxxxxxx',
                'qr_image_path' => null,
                'instructions' => 'Transfer the exact order total and upload the payment screenshot.',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::query()->updateOrCreate([
                'code' => $method['code'],
            ], $method);
        }
    }
}
