<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'jenis_pemesanan' => 'required|in:' . implode(',', ['antar_jemput', 'antar_mandiri']),
            'nama_pemesan' => 'required|string',
            'nomor_telepon' => 'required|string|max:255',
            'metode_pembayaran' => 'required|string',
            'tanggal_pemesanan' => 'nullable|date',
            'tanggal_pengambilan' => 'nullable|date',
            'laundry_id' => 'required|integer|exists:laundries,id',
            'address_id' => 'required|integer|exists:addresses,id',
        ];
    }
}
