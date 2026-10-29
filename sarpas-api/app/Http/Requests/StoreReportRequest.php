<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location_id'          => ['required', 'integer', 'exists:locations,id'],
            'facility_category_id' => ['required', 'integer', 'exists:facility_categories,id'],
            'deskripsi'            => ['required', 'string', 'min:10', 'max:1000'],
            'urgensi'              => ['required', 'in:rendah,sedang,mendesak'],
            'photos'               => ['nullable', 'array', 'max:2'],
            'photos.*'             => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
}
