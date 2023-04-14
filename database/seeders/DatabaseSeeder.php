<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ExtraGroup;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Stock;
use App\Models\Subscription;
use App\Models\User;
use Database\Factories\OrderFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

//        $this->call(LanguageSeeder::class);
//        $this->call(CurrencySeeder::class);
//        $this->call(RoleSeeder::class);
//        $this->call(SubscriptionSeeder::class);
//        $this->call(TranslationSeeder::class);
        $this->call(UserSeeder::class);
//        $this->call(PaymentSeeder::class);
//        $this->call(SmsGatewaySeeder::class);
//        $this->call(UnitSeeder::class);


//        if (app()->environment() == 'local') {
//            Category::factory()->hasTranslations(1)->count(10)->create();
//            Brand::factory()->count(10)->create();
//            ExtraGroup::factory()->hasTranslation(1)->hasExtraValues(3)->count(5)->create();
//            User::factory()->has(
//                Shop::factory()->hasTranslation(1)->has(
//                    Product::factory()->hasTranslation(1)->has(
//                        Stock::factory()->hasStockExtras(4)->count(rand(1, 3))
//                    )->hasExtras(2)->hasProperties(10)->count(rand(10,30))
//                )->count(1)
//            )->count(100)->create();
//            Order::factory()->has(OrderDetail::factory()->hasProducts(2)->count(3))->count(10)->create();
//        }
    }
}
