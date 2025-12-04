<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'resident' => new ResidentResource($this->whenLoaded('resident')),
            'invoice_date' => $this->invoice_date->format('Y-m-d'),
            'iuran_amount' => $this->iuran_amount,
            'peturunan_amount' => $this->peturunan_amount,
            'dedosan_amount' => $this->dedosan_amount,
            'total_amount' => $this->total_amount,
            'user' => $this->when($this->user, [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ]),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
