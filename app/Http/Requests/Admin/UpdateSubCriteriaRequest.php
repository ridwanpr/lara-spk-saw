<?php

namespace App\Http\Requests\Admin;

use App\Models\SubCriteria;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        /** @var SubCriteria|null $subCriteria */
        $subCriteria = $this->route('subCriteria') ?? $this->route('sub_criterion');

        return [
            'edit_description' => ['required', 'string', 'max:255'],
            'edit_crisp_value' => [
                'required',
                'integer',
                'min:1',
                'max:5',
                Rule::unique('sub_criterias', 'crisp_value')
                    ->where(function ($query) use ($subCriteria) {
                        return $query->where('criteria_id', $subCriteria?->criteria_id);
                    })
                    ->ignore($subCriteria?->id),
            ],
            'edit_min_value' => ['nullable', 'numeric'],
            'edit_max_value' => ['nullable', 'numeric'],
        ];
    }

    public function attributes(): array
    {
        return [
            'edit_description' => 'Nama sub kriteria',
            'edit_crisp_value' => 'Nilai crisp',
            'edit_min_value' => 'Nilai minimum',
            'edit_max_value' => 'Nilai maksimum',
        ];
    }
}
