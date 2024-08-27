<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderChartWeeklyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_orders' => $this->total_transactions,
            'total_income' => $this->total_income,
            'created_at' => $this->created_at->translatedFormat('l'),
        ];
    }
}
