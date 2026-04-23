<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFindingRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'kode_temuan' => 'required|string',
            'uraian_temuan' => 'required|string',
            'kerugian_negara' => 'required|numeric|min:0',
            'kerugian_daerah' => 'required|numeric|min:0',
        ];
    }
}
