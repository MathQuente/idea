<?php

namespace App\Http\Requests;

use App\IdeaStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IdeaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('steps')) {
            $this->merge([
                'steps' => collect($this->input('steps'))->map(fn($step) => [
                    ...$step,
                    'completed' => filter_var($step['completed'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ])->all(),
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(IdeaStatus::class)],
            'links' => ['nullable', 'array'],
            'links.*' => ['url', 'max:225'],
            'steps' => ['nullable', 'array'],
            'steps.*.description' => ['required', 'string', 'max:225'],
            'steps.*.completed' => ['boolean'],
            'image' => ['nullable', 'image', 'max:5120']
        ];
    }
}
