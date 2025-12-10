<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\InvoiceResource;

class PaymentResource extends JsonResource
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
            'invoice_id' => $this->invoice_id,
            'amount' => $this->amount, // Raw value or casted by model
            'date' => $this->date?->format('Y-m-d'),
            'method' => $this->method,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            // Relationships
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
