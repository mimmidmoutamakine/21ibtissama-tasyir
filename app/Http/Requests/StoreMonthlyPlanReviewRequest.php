<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMonthlyPlanReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:approved,rejected,revision'],
            'remarks' => ['nullable', 'string'],
            'changes_summary' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'decision.required' => 'قرار المراجعة مطلوب.',
            'decision.in' => 'قرار المراجعة غير صالح.',
        ];
    }
}