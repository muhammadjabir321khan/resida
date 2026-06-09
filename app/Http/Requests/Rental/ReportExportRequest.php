<?php

namespace App\Http\Requests\Rental;

use App\Models\Property;
use App\Services\LandlordReportService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(LandlordReportService::types())],
            'format' => ['required', 'string', Rule::in(['pdf', 'csv'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'property_id' => [
                'nullable',
                'integer',
                Rule::exists(Property::class, 'id')->where('user_id', (int) $this->user()->id),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $pid = $this->input('property_id');
        if ($pid === '' || $pid === null) {
            $this->merge(['property_id' => null]);
        }
    }
}
