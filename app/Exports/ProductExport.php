<?php

namespace App\Exports;

use App\Models\Currency;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductExport implements FromCollection,WithHeadings
{
    public function __construct($shop_id)
    {
        $this->shop_id = $shop_id;
        $this->lang = request('lang') ?? null;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $model = Product::with([
            'category.translation' => fn($q) => $q->where('locale', $this->lang),
            'translation' => fn($q) => $q->where('locale', $this->lang),
        ])->where('shop_id', $this->shop_id)->get();
        return $model->map(function ($model){
            return $this->productModel($model);
        });
    }

    public function headings(): array
    {
        return [
            'Təsvir',
            'Məhsulun adı',
            'Xüsusiyyətlər',
            'Kateqoriya',
            'Barkod',
        ];
    }

    private function productModel($item): array
    {
        return [
            'Təsvir' => 'https://api.mupza.com/storage/images/'.$item->img,
            'Məhsulun adı' =>  $item->translation ? $item->translation->title : '',
            'Xüsusiyyətlər' =>  $item->translation ? $item->translation->description : '',
            'Kateqoriya' => $item->category ? $item->category->translation->title : '',
            'Barkod' => $item->bar_code,
        ];
    }
}
