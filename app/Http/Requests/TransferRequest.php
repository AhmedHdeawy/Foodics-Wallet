<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
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
        return [
            'client_id' => ['required', 'numeric'],
            'receiver_account_number' => ['required', 'string', 'max:50'],
            'receiver_bank_code' => ['required', 'string', 'max:50'],
            'beneficiary_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
            'reference' => ['required', 'string', 'max:255'],
            'notes' => ['sometimes', 'array'],
            'notes.*' => ['string', 'max:255'],
            'payment_type' => ['sometimes', 'string', 'max:50'],
            'charge_details' => ['sometimes', 'string', 'max:50'],
        ];
    }
}
