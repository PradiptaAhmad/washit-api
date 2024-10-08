<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoryDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'no_pemesanan' => $this->no_pemesanan,
            'jenis_pemesanan' => $this->jenis_pemesanan,
            'nama_pemesan' => $this->nama_pemesan,
            'nomor_telepon' => $this->nomor_telepon,
            'alamat' => $this->alamat,
            'metode_pembayaran' => $this->metode_pembayaran,
            'berat_laundry' => $this->berat_laundry,
            'total_harga' => $this->total_harga,
            'tanggal_pemesanan' => $this->created_at,
            'tanggal_pengambilan' => $this->tanggal_pengambilan,
            'tanggal_estimasi' => $this->tanggal_estimasi,
            'catatan' => $this->catatan,
            'status' => $this->status,
            'laundry_service' => $this->laundry_service,
            'laundry_description' => $this->laundry_description,
            'laundry_price' => $this->laundry_price,
            'user_id' => $this->user_id,
        ];
    }
}
