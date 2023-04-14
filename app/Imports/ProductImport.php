<?php

namespace App\Imports;

ini_set('memory_limit', '16000M');
ini_set('max_execution_time', 999);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Discount;
use App\Models\ExtraGroup;
use App\Models\ExtraValue;
use App\Models\Gallery;
use App\Models\Product;
use App\Models\Stock;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation, SkipsOnFailure,ShouldQueue
{

    public function __construct($shop_id)
    {
        $this->shop_id = $shop_id;
    }

    use Importable, ApiResponse, SkipsFailures;


    /**
     * @param Collection $collection
     * @return void
     */
    public function collection(Collection $collection)
    {
        $lang = 'aze';

        $langTr = 'tr';

        $langRu = 'ru';

        $langEn = 'en';

        foreach ($collection as $row) {

            // Parent category
            $parentCategory = Category::query()->whereHas('translations', fn($q) => $q->where('locale', $lang)
                ->where('title', $row['kateqoriya']))->where('parent_id',0)->first();

            if (!$parentCategory) {

                $parentCategory = Category::create([
                    'parent_id' => 0
                ]);

                $parentCategory->translation()->create([
                    'locale' => $lang,
                    'title'  => $row['kateqoriya']
                ]);

                if (isset($row['kateqoriya_tr'])) {

                    $parentCategory->translation()->create([
                        'locale' => $langTr,
                        'title'  => $row['kateqoriya_tr']
                    ]);

                }

                if (isset($row['kateqoriya_ru'])) {

                    $parentCategory->translation()->create([
                        'locale' => $langRu,
                        'title'  => $row['kateqoriya_ru']
                    ]);

                }

                if (isset($row['kateqoriya_en'])) {

                    $parentCategory->translation()->create([
                        'locale' => $langEn,
                        'title'  => $row['kateqoriya_en']
                    ]);

                }
            }

            // Category
            $category = Category::query()->whereHas('translations', fn($q) => $q->where('locale', $lang)
                ->where('title', $row['alt_kateqoriya']))->where('parent_id', $parentCategory->id)->first();
            if (!$category) {

                $category = Category::create([
                    'parent_id' => $parentCategory->id
                ]);

                $category->translation()->create([
                    'locale' => $lang,
                    'title'  => $row['alt_kateqoriya']
                ]);

                if (isset($row['alt_kateqoriya_tr'])) {

                    $category->translation()->create([
                        'locale' => $langTr,
                        'title'  => $row['alt_kateqoriya_tr']
                    ]);

                }

                if (isset($row['alt_kateqoriya_ru'])) {

                    $category->translation()->create([
                        'locale' => $langRu,
                        'title'  => $row['alt_kateqoriya_ru']
                    ]);

                }

                if (isset($row['alt_kateqoriya_en'])) {

                    $category->translation()->create([
                        'locale' => $langEn,
                        'title' => $row['alt_kateqoriya_en']
                    ]);

                }
            }

            $childCategory = Category::query()
                ->whereHas('translations', fn($q) => $q->where('locale', $lang)
                    ->where('title', $row['bolme']))->where('parent_id', $category->id)
                ->first();

            // Child category

            if (!$childCategory) {

                $childCategory = Category::query()->create([
                    'parent_id' => $category->id,
//                    'weight' => $row['weight']
                ]);

                $childCategory->translation()->create([
                    'locale' => $lang,
                    'title'  => $row['bolme']

                ]);

                if (isset($row['bolme_tr'])) {

                    $childCategory->translation()->create([
                        'locale' => $langTr,
                        'title'  => $row['bolme_tr']
                    ]);

                }

                if (isset($row['bolme_ru'])) {

                    $childCategory->translation()->create([
                        'locale' => $langRu,
                        'title'  => $row['bolme_ru']
                    ]);

                }

                if (isset($row['bolme_en'])) {

                    $childCategory->translation()->create([
                        'locale' => $langEn,
                        'title' => $row['bolme_en']
                    ]);

                }
            }
            if (isset($row['marka'])) {
                $brand = Brand::query()
                    ->firstOrCreate([
                        'title' => $row['marka'],
                    ], [
                        'active' => 1,
                    ]);
            }

            $product = Product::where('bar_code', $row['barkod'])->where('shop_id',$this->shop_id)->first();

            if (!$product)
            {
                $product = Product::create([
                    'category_id' => $childCategory->id,
                    'bar_code'    => $row['barkod'] ?? null,
                    'shop_id'     => $this->shop_id,
                    'unit_id'     => 1,
                    'brand_id'    => $brand->id ?? 6,
                    'min_qty'     => 1,
                    'max_qty'     => 100,
                    'tax'         => 0,
                    'active'      => 1
                ]);

                $product->translation()->create([
                    'locale' => $lang,
                    'title' => $row['mehsulun_adi'],
                    'description' => $row['xususiyyetler'] ?? null
                ]);

                if (isset($row['mehsulun_adi_tr']))
                {
                    $product->translation()->create([
                        'locale'      => $langTr,
                        'title'       => $row['mehsulun_adi_tr'],
                        'description' => $row['xususiyyetler_tr'] ?? null
                    ]);
                }

                if (isset($row['mehsulun_adi_ru']))
                {
                    $product->translation()->create([
                        'locale'      => $langRu,
                        'title'       => $row['mehsulun_adi_ru'],
                        'description' => $row['xususiyyetler_ru'] ?? null
                    ]);
                }

                if (isset($row['mehsulun_adi_en']))
                {
                    $product->translation()->create([
                        'locale'      => $langEn,
                        'title'       => $row['mehsulun_adi_en'],
                        'description' => $row['xususiyyetler_en'] ?? null
                    ]);
                }

            }

            if (isset($row['tesvir'])) {

                $product->galleries()->delete();

                $images = explode('https', $row['tesvir']);


                foreach ($images as $image) {

                    if (empty($image)) {
                        continue;
                    }

                    try {
                        $imageRemoveCharacter = preg_replace('/([\r\n\t])/', '', $image);

                        $contents = file_get_contents('https' . $imageRemoveCharacter,'test');

                        $randomStr = Str::random(6);

                        $name = 'products/' . Str::slug(Carbon::now()->format('Y-m-d h:i:s')) . $randomStr . '.' . substr(strrchr($imageRemoveCharacter, "."), 1);

//                        Storage::put('public/images/' . $name, $contents);
                        Storage::disk('do')->put('public/images/' . $name, $contents,'public');

                        Gallery::create([
                            'title' => Str::of($name)->after('/'),
                            'path' => $name,
                            'type' => 'products',
                            'loadable_type' => 'App\Models\Product',
                            'loadable_id' => $product->id,
                        ]);


                    } catch (\Throwable $e) {
                        Log::error('failed img upload', [
                            'url' => 'https' . $imageRemoveCharacter,
                            'message' => $e->getMessage(),
                        ]);
                    }
                }

                $product->update(['img' => data_get($product->galleries->first(), 'path')]);
            }


            $quantity = isset($row['movcudlugu']) && $row['movcudlugu'] == 'Да' ? 100 : 1;
            $rate = 1;

            if (isset($row['valyuta'])) {
                $rate = Currency::where('short_code', $row['valyuta'])->first()->rate;
            }
            $price = 0;
            if (isset($row['qiymet'])) {
                $price = round(str_replace(',', '.', $row['qiymet']) / $rate, 2);
            }

            if (isset($row['kohne_qiymet'])) {

                    $oldPrice = round(str_replace(',', '.', $row['kohne_qiymet']) / $rate, 2);

                    if ($oldPrice > $price)
                    {
                        $fixPrice = $oldPrice - $price;

                        $price = $oldPrice;

                        $discount = Discount::where('shop_id', $this->shop_id)->where('price', $fixPrice)
                            ->where('type', 'fix')->first();

                        if (!$discount) {

                            $discount = Discount::create([
                                'shop_id' => $this->shop_id,
                                'type'    => 'fix',
                                'price'   => $fixPrice,
                                'active'  => 1,
                                'start'   => now(),
                                'end'     => now()->addYears(1)
                            ]);

                        }

                        $product->discount()->attach(['discount_id' => $discount->id]);
                    }
            }

            $extraGroupSize = ExtraGroup::whereHas('translations', fn($q) => $q->where('locale', $lang)
                ->where('title', 'Ölçü'))->first();

            if (!$extraGroupSize) {

                $extraGroupSize = ExtraGroup::create([
                    'type' => 'text',
                    'active' => 1
                ]);

                $extraGroupSize->translation()->create([
                    'locale' => $lang,
                    'title' => 'Ölçü',
                ]);
            }


            $extraGroupColor = ExtraGroup::whereHas('translations', fn($q) => $q->where('locale', $lang)
                ->where('title', 'Rəng'))->first();

            if (!$extraGroupColor) {

                $extraGroupColor = ExtraGroup::create([
                    'type' => 'color',
                    'active' => 1
                ]);

                $extraGroupColor->translation()->create([
                    'locale' => $lang,
                    'title' => 'Rəng',
                ]);

                $extraGroupColor->translation()->create([
                    'locale' => $langTr,
                    'title' => 'Renk',
                ]);

            }

            $product->extras()->detach();

            if (isset($row['olcu']))
            {

                $product->extras()->attach(['extra_group_id' => $extraGroupSize->id]);
            }

            if (isset($row['reng']))
            {
                $product->extras()->attach(['extra_group_id' => $extraGroupColor->id]);
            }

            if ($product->stocks)
            {
                $product->stocks()->delete();
            }

            if (!isset($row['olcu']) && !isset($row['reng']))
            {
                Stock::create([
                    'price'          => $price,
                    'quantity'       => $quantity,
                    'url'            => $row['url'] ?? null,
                    'countable_id'   => $product->id,
                    'countable_type' => 'App\Models\Product'
                ]);
            }

            $params = [
                'extraGroupColorId' => $extraGroupColor->id,
                'price' => $price,
                'quantity' => $quantity,
                'productId' => $product->id,
                'url' => $row['url'] ?? null
            ];

                    if (isset($row['olcu'])) {

                        $sizeArray = explode("\n", $row['olcu']);

                        foreach ($sizeArray as $size) {

                            $extraValueSize = ExtraValue::where('value', $size)->where('extra_group_id',$extraGroupSize->id)->first();

                            if (!$extraValueSize) {

                                $extraValueSize = ExtraValue::create([
                                    'extra_group_id' => $extraGroupSize->id,
                                    'value'          => $size,
                                    'active'         => 1
                                ]);

                            }

                            if (isset($row['reng'])) {
                                $params['value'] = $row['reng'];

                                $params['extraValueSizeId'] = $extraValueSize->id;

                                $this->addExtraValueColor($params);

                            }else{

                                $stock = Stock::create([
                                    'price'          => $price,
                                    'quantity'       => $quantity,
                                    'url'            => $row['url'] ?? null,
                                    'countable_id'   => $product->id,
                                    'countable_type' => 'App\Models\Product'
                                ]);

                                $stock->stockExtras()->attach(['extra_value_id' => $extraValueSize->id]);
                            }
                        }

                    } elseif(isset($row['reng'])) {

                        $params['value'] = $row['reng'];

                        $this->addExtraValueColor($params);

                    }

                }
            }



    private function addExtraValueColor(array $params)
    {
        $colorArray = explode("\n", $params['value']);

        foreach ($colorArray as $color)
        {

            $color = data_get(collect(config('colors'))->where('name', $color)->first(), 'key', $color);

            $extraValueColor = ExtraValue::where('value', $color)->where('extra_group_id',$params['extraGroupColorId'])->first();

            if (!$extraValueColor) {

                $extraValueColor = ExtraValue::create([
                    'extra_group_id' => $params['extraGroupColorId'],
                    'value'          => $color,
                    'active'         => 1
                ]);

            }

            $stock = Stock::create([
                'price'          => $params['price'],
                'quantity'       => $params['quantity'],
                'url'            => $params['url'],
                'countable_id'   => $params['productId'],
                'countable_type' => 'App\Models\Product'
            ]);


            $stock->stockExtras()->attach(['extra_value_id' => $extraValueColor->id]);

            if (isset($params['extraValueSizeId']))
            {
                $stock->stockExtras()->attach(['extra_value_id' => $params['extraValueSizeId']]);
            }

        }
    }

    public function rules(): array
    {
        return [
            'kateqoriya'     => ['required'],
            'alt_kateqoriya' => ['required'],
            'bolme'          => ['required'],
            'mehsulun_adi'   => ['required'],
        ];
    }


    public function headingRow(): int
    {
        return 1;
    }

    public function batchSize(): int
    {
        return 10;
    }

    public function chunkSize(): int
    {
        return 10;
    }

//    public function registerEvents(): array
//    {
//        return [
//            ImportFailed::class => function(ImportFailed $event) {
//                $this->importedBy->notify(new ImportHasFailedNotification);
//            },
//        ];
//    }


}
