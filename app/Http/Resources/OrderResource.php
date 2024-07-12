<?php

namespace App\Http\Resources;

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
            'no_pemesanan' => $this->no_pemesanan,
            'nama_laundry' => optional($this->laundry)->nama_laundry,
            'nama_pemesan' => $this->nama_pemesan,
            'berat_laundry' => $this->berat_laundry,
            'total_harga' => $this->total_harga,
        ];
    }
}
