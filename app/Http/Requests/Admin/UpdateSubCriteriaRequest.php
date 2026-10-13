<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSubCriteriaRequest extends FormRequest
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
            'edit_description' => ['required', 'string', 'max:255'],
            'edit_crisp_value' => ['required', 'integer', 'min:1', 'max:5'],
            'edit_min_value' => ['numeric'],
            'edit_max_value' => ['numeric'],
        ];
    }

    public function attributes()
    {
        return [
            'edit_description' => 'Nama sub kriteria',
            'edit_crisp_value' => 'Nilai crips',
            'edit_min_value' => 'Nilai minimum',
            'edit_max_value' => 'Nilai maksimum',
        ];
    }
}
