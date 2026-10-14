<?php

namespace App\Http\Requests\Shared;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreRepairTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'asset_id' => [
                'required',
                'integer',
                Rule::exists('assets', 'id'),
            ],
            'damage_description' => [
                'required',
                'string',
                'min:3',
                'max:2000',
            ],
            'damage_photo' => [
                'nullable',
                File::image()
                    ->types(['jpeg', 'png', 'jpg', 'webp'])
                    ->max(5 * 1024), // Max 5MB
            ],
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
            'asset_id' => 'aset',
            'damage_description' => 'deskripsi kerusakan',
            'damage_photo' => 'bukti foto',
        ];
    }
}
