<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLhpRequest extends FormRequest
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
            'lhp.nomor_lhp' => 'required|string|unique:lhps,nomor_lhp',
            'lhp.tgl_lhp' => 'required|date',
            'lhp.judul' => 'required|string',
            'lhp.tahun_anggaran' => 'required|digits:4',
            'lhp.opd_id' => 'required|uuid|exists:opds,id',
            'content.bab_1_info_umum' => 'nullable|string',
            'content.bab_2_hasil_audit' => 'nullable|string',
            'content.bab_3_penutup' => 'nullable|string',
            'teams' => 'nullable|array',
            'teams.*.user_id' => 'required|uuid|exists:users,id',
            'teams.*.role' => 'required|in:penanggung_jawab,pengendali_teknis,ketua_tim,anggota',
        ];
    }
}
