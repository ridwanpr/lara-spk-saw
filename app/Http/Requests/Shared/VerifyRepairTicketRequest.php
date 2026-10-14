<?php

namespace App\Http\Requests\Shared;

use App\Enums\CriteriaInputType;
use App\Models\Criteria;
use App\Models\SubCriteria;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyRepairTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() || $this->user()?->isInspector();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'estimated_cost' => ['required', 'numeric', 'min:0'],
            'evaluations' => ['required', 'array'],
        ];

        // Ambil kriteria yang diinput manual (non-sistem + C1)
        $manualCriterias = Criteria::query()
            ->where('is_system', false)
            ->orWhere('code', 'C1')
            ->get();

        foreach ($manualCriterias as $criteria) {
            $key = "evaluations.{$criteria->id}";

            if ($criteria->input_type === CriteriaInputType::Categorical) {
                $rules[$key] = [
                    'required',
                    'integer',
                    Rule::exists(SubCriteria::class, 'id')->where('criteria_id', $criteria->id),
                ];
            } else {
                $rules[$key] = ['required', 'numeric', 'min:0'];
            }
        }

        return $rules;
    }

    /**
     * Custom validation attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [
            'estimated_cost' => 'Estimasi Biaya',
            'evaluations' => 'Nilai Evaluasi',
        ];

        $manualCriterias = Criteria::query()
            ->where('is_system', false)
            ->orWhere('code', 'C1')
            ->get();

        foreach ($manualCriterias as $criteria) {
            $attributes["evaluations.{$criteria->id}"] = $criteria->name;
        }

        return $attributes;
    }
}
