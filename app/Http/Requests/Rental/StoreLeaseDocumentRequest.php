<?php

namespace App\Http\Requests\Rental;

use App\Models\LeaseDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeaseDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in([
                LeaseDocument::CATEGORY_LEASE,
                LeaseDocument::CATEGORY_MOVE_IN,
                LeaseDocument::CATEGORY_IDENTIFICATION,
                LeaseDocument::CATEGORY_OTHER,
            ])],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'is_visible_to_tenant' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_visible_to_tenant' => $this->boolean('is_visible_to_tenant', true),
        ]);
    }
}
