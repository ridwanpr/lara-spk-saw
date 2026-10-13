<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubCriteriaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'crisp_value' => ['required', 'integer', 'min:1', 'max:5'],
            'min_value' => ['numeric'],
            'max_value' => ['numeric'],
        ];
    }

    public function attributes()
    {
        return [
            'description' => 'Nama sub kriteria',
            'crisp_value' => 'Nilai crips',
            'min_value' => 'Nilai minimum',
            'max_value' => 'Nilai maksimum',
        ];
    }
}
