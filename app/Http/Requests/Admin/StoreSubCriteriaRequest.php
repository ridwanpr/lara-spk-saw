<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $criteriaId = $this->route('criteria')?->id;

        return [
            'description' => ['required', 'string', 'max:255'],
            'crisp_value' => [
                'required',
                'integer',
                'min:1',
                'max:5',
                Rule::unique('sub_criterias', 'crisp_value')->where(function ($query) use ($criteriaId) {
                    return $query->where('criteria_id', $criteriaId);
                }),
            ],
            'min_value' => ['nullable', 'numeric'],
            'max_value' => ['nullable', 'numeric'],
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
