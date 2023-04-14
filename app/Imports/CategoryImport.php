<?php

namespace App\Imports;

use App\Models\Category;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CategoryImport implements ToCollection, WithHeadingRow, WithBatchInserts
{
    use Importable;

    /**
     * @var array|Application|Request|string
     */
    protected $lang;

    public function __construct()
    {
        $this->lang = request('lang') ?? 'aze';
    }

    /**
     * @param Collection $collection
     * @return mixed
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {

            if (isset($row['category_name'])) {

                $category = Category::whereHas('translation', function ($q) use ($row) {
                    $q->where('locale', $this->lang)->where('title', $row['category_name']);
                })->first();

                if (!$category) {
                    $category = Category::create([
                        'keywords' => $row['category_name']
                    ]);
                    $category->translation()->create([
                        'locale' => $this->lang,
                        'title' => $row['category_name'],
                    ]);
                }

                if (isset($row['subcategory_name'])) {

                    $subCategory = Category::whereHas('translation', function ($q) use ($row) {
                        $q->where('locale', $this->lang)->where('title', $row['subcategory_name']);
                    })->first();

                    if (!$subCategory) {
                        $subCategory = Category::create([
                            'parent_id' => $category->id,
                            'keywords' => $row['subcategory_name']
                        ]);
                        $subCategory->translation()->create([
                            'locale' => $this->lang,
                            'title' => $row['subcategory_name'],
                        ]);
                    }


                    if (isset($row['section_name'])) {

                        $section = Category::whereHas('translation', function ($q) use ($row) {
                            $q->where('locale', $this->lang)->where('title', $row['section_name']);
                        })->first();

                        if (!$section) {
                            $section = Category::create([
                                'parent_id' => $subCategory->id,
                                'keywords' => $row['section_name']
                            ]);
                            $section->translation()->create([
                                'locale' => $this->lang,
                                'title' => $row['section_name'],
                            ]);
                        }
                    }

                }
            }

        }
        return true;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function batchSize(): int
    {
        return 500;
    }
}
