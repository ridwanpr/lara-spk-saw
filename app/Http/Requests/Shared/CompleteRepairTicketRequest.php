<?php

namespace App\Http\Requests\Shared;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CompleteRepairTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) ($this->user()?->isAdmin() || $this->user()?->isInspector());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'actual_cost' => ['required', 'numeric', 'min:0'],
            'completion_notes' => ['nullable', 'string', 'max:2000'],
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
            'actual_cost' => 'Biaya Realisasi',
            'completion_notes' => 'Catatan Perbaikan',
        ];
    }
}
