<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionChartWeeklyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_transactions' => $this->total_transactions,
            'total_income' => $this->total_income,
            'created_at' => $this->created_at->translatedFormat('l'),
        ];
    }
}
