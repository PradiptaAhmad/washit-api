<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoryResource extends JsonResource
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
            'nama_laundry' => $this->laundry_service,
            'jenis_pemesanan' => $this->jenis_pemesanan,
            'alamat' => $this->alamat,
            'tanggal_estimasi' => $this->tanggal_estimasi,
            'tanggal_pemesanan' => $this->created_at,
            'nama_pemesan' => $this->nama_pemesan,
            'berat_laundry' => $this->berat_laundry,
            'total_harga' => $this->total_harga,
            'status' => $this->status,
        ];
    }
}
