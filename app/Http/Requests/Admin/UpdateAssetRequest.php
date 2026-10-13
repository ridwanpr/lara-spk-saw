<?php

namespace App\Http\Requests\Admin;

use App\Enums\AssetCondition;
use App\Models\Asset;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim((string) $this->input('code'))),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Asset|null $asset */
        $asset = $this->route('asset');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique(Asset::class, 'code')->ignore($asset?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:asset_categories,id'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'procurement_year' => ['required', 'integer', 'min:1990', 'max:'.((int) date('Y') + 1)],
            'condition' => ['required', Rule::enum(AssetCondition::class)],
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
            'code' => 'Kode Aset',
            'name' => 'Nama Aset',
            'category_id' => 'Kategori Aset',
            'room_id' => 'Ruangan',
            'procurement_year' => 'Tahun Pengadaan',
            'condition' => 'Kondisi Aset',
        ];
    }
}
