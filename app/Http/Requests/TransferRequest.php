<?php

namespace App\Http\Requests;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'value' => 'required|numeric|min:0.01',
            'payer' => 'required|exists:usuarios,id',
            'payee' => 'required|exists:usuarios,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'value.required' => 'O valor da transferência é obrigatório.',
            'value.numeric'  => 'O valor deve ser numérico.',
            'value.min'      => 'O valor mínimo para transferência é R$ 0.01.',
            'payer.required' => 'O pagador é obrigatório.',
            'payer.exists'   => 'O pagador não foi encontrado.',
            'payee.required' => 'O recebedor é obrigatório.',
            'payee.exists'   => 'O recebedor não foi encontrado.',
        ];
    }
}
