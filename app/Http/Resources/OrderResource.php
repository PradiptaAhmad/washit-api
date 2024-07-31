<?php

namespace App\Http\Resources;

use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'nama_laundry' => optional($this->laundry)->nama_laundry,
            'jenis_pemesanan' => $this->jenis_pemesanan,
            'alamat' => $this->alamat,
            'tanggal_estimasi' => $this->tanggal_estimasi,
            'tanggal_pemesanan' => $this->created_at,
            'nama_pemesan' => $this->nama_pemesan,
            'berat_laundry' => $this->berat_laundry,
            'total_harga' => $this->total_harga,
            'status' => OrderStatus::where('order_id', $this->id)->latest()->first()->status_description,
        ];
    }
}
