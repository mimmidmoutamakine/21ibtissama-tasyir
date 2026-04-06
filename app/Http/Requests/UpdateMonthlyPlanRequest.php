<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMonthlyPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer', 'exists:monthly_plan_items,id'],
            'items.*.annual_project_objective_id' => ['nullable', 'integer'],
            'items.*.domain_axis' => ['required', 'string', 'max:255'],
            'items.*.monthly_objective' => ['required', 'string'],
            'items.*.activity' => ['required', 'string'],
            'items.*.resources' => ['nullable', 'string'],
            'items.*.success_criteria' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'خاص تكون على الأقل بند واحد في الخطة.',
            'items.min' => 'خاص تكون على الأقل بند واحد في الخطة.',
            'items.*.domain_axis.required' => 'المجال مطلوب.',
            'items.*.monthly_objective.required' => 'الهدف الشهري مطلوب.',
            'items.*.activity.required' => 'النشاط مطلوب.',
        ];
    }
}