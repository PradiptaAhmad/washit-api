<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderAdminDetailResource extends JsonResource
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
            'tanggal_pemesanan' => $this->tanggal_pemesanan,
            'tanggal_pengambilan' => $this->tanggal_pengambilan,
            'laundry_service' => optional($this->laundry)->first()->only(['id', 'nama_laundry', 'alamat', 'nomor_telepon']),
            'user' => optional($this->user)->first()->only(['id', 'username', 'email', 'phone']),
        ];
    }
}
