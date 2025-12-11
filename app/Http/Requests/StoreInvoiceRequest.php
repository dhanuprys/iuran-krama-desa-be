<?php

namespace App\Http\Requests;

class StoreInvoiceRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resident_id' => 'required|integer|exists:residents,id',
            'invoice_date' => 'required|date|before_or_equal:today',
            'peturunan_amount' => 'required|numeric|min:0|max:999999.99',
            'dedosan_amount' => 'required|numeric|min:0|max:999999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'resident_id.required' => 'ID penduduk wajib diisi.',
            'resident_id.integer' => 'ID penduduk harus berupa angka.',
            'resident_id.exists' => 'Penduduk yang dipilih tidak ditemukan.',
            'invoice_date.required' => 'Tanggal invoice wajib diisi.',
            'invoice_date.date' => 'Format tanggal invoice tidak valid.',
            'invoice_date.before_or_equal' => 'Tanggal invoice tidak boleh di masa depan.',
            'peturunan_amount.required' => 'Jumlah peturunan wajib diisi.',
            'peturunan_amount.numeric' => 'Jumlah peturunan harus berupa angka.',
            'peturunan_amount.min' => 'Jumlah peturunan tidak boleh kurang dari 0.',
            'peturunan_amount.max' => 'Jumlah peturunan tidak boleh melebihi 999.999,99.',
            'dedosan_amount.required' => 'Jumlah dedosan wajib diisi.',
            'dedosan_amount.numeric' => 'Jumlah dedosan harus berupa angka.',
            'dedosan_amount.min' => 'Jumlah dedosan tidak boleh kurang dari 0.',
            'dedosan_amount.max' => 'Jumlah dedosan tidak boleh melebihi 999.999,99.',
        ];
    }
}
