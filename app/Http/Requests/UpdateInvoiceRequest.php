<?php

namespace App\Http\Requests;

class UpdateInvoiceRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resident_id' => 'sometimes|integer|exists:residents,id',
            'invoice_date' => 'sometimes|date|before_or_equal:today',
            'peturunan_amount' => 'sometimes|numeric|min:0|max:999999.99',
            'dedosan_amount' => 'sometimes|numeric|min:0|max:999999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'resident_id.integer' => 'ID penduduk harus berupa angka.',
            'resident_id.exists' => 'Penduduk yang dipilih tidak ditemukan.',
            'invoice_date.date' => 'Format tanggal invoice tidak valid.',
            'invoice_date.before_or_equal' => 'Tanggal invoice tidak boleh di masa depan.',
            'peturunan_amount.numeric' => 'Jumlah peturunan harus berupa angka.',
            'peturunan_amount.min' => 'Jumlah peturunan tidak boleh kurang dari 0.',
            'peturunan_amount.max' => 'Jumlah peturunan tidak boleh melebihi 999.999,99.',
            'dedosan_amount.numeric' => 'Jumlah dedosan harus berupa angka.',
            'dedosan_amount.min' => 'Jumlah dedosan tidak boleh kurang dari 0.',
            'dedosan_amount.max' => 'Jumlah dedosan tidak boleh melebihi 999.999,99.',
        ];
    }
}
