<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class WebhookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Configure the validator instance.
     *
     * @param  Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Ensure the content isn't empty
            if (empty($this->getContent())) {
                $validator->errors()->add('content', 'The webhook content cannot be empty.');
            }
        });
    }

    public function validated($key = null, $default = null)
    {
        return array_merge(parent::validated(), [
            'raw_data' => $this->getContent(),
            'bank_name' => $this->route('bank'),
        ]);
    }
}
