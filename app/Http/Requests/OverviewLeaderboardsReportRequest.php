<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OverviewLeaderboardsReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $available = ['products', 'customers', 'categories','shops'];

        return [
            'date_from'    => 'required|date|before_or_equal:date_to',
            'date_to'      => 'required|date|before_or_equal:' . now()->format('Y-m-d'),
            'leaderboards' => 'array|in:' . implode(',', $available),
            'sort'         => ['array',
                function ($attribute, $value, $fail) use ($available) {
                    foreach ($value ?? [] as $key => $item) {
                        if (!in_array($key, $available)) {
                            $fail('there is no such' . $key . '. Available:' . implode(',', $available));
                        } elseif (!in_array($item, ['ASC', 'asc', 'DESC', 'desc'])) {
                            $fail(__('validation.in', ['attribute' => 'sort']));
                        }
                    }
                }],
            'column'       => ['array',
                function ($attribute, $value, $fail) use ($available) {
                    foreach ($value ?? [] as $key => $item) {
                        if (!in_array($key, $available)) {
                            $fail('there is no such' . $key . '. Available:' . implode(',', $available));
                        }
                    }
                }],
        ];
    }
}
