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
            'jenis_pemesanan' => $this->jenis_pemesanan,
            'nama_pemesan' => $this->nama_pelanggan,
            'nomor_telepon' => $this->nomor_telepon,
            'alamat' => $this->alamat,
            'berat_laundry' => $this->berat_laundry,
            'total_harga' => $this->total_harga,
            'payment_method' => $this->payment_method,
            'tanggal_pemesanan' => $this->tanggal_pemesanan,
            'tanggal_pengambilan' => $this->tanggal_pengambilan,
            'transactions' => new TransactionResource($this->transactions),
        ];
    }
}
