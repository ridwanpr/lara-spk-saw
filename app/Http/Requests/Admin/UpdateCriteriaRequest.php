<?php

namespace App\Http\Requests\Admin;

use App\Enums\CriteriaInputType;
use App\Enums\CriteriaType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCriteriaRequest extends FormRequest
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
        $criteriaId = $this->route('criteria')?->id ?? $this->route('criteria');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('criterias', 'code')->ignore($criteriaId)],
            'type' => ['required', Rule::enum(CriteriaType::class)],
            'weight' => ['required', 'numeric', 'gt:0', 'max:1'],
            'input_type' => ['required', Rule::enum(CriteriaInputType::class)],
        ];
    }

    /**
     * Custom validation attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama Kriteria',
            'code' => 'Kode Kriteria',
            'type' => 'Atribut Kriteria',
            'weight' => 'Bobot Kriteria',
            'input_type' => 'Tipe Penilaian',
        ];
    }
}
