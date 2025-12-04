<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResidentResource extends JsonResource
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
            'nik' => $this->nik,
            'name' => $this->name,
            'gender' => $this->gender,
            'resident_status' => $this->whenLoaded('residentStatus', [
                'id' => $this->residentStatus?->id,
                'name' => $this->residentStatus?->name,
                'contribution_amount' => $this->residentStatus?->contribution_amount,
            ]),
            'banjar' => $this->whenLoaded('banjar', [
                'id' => $this->banjar?->id,
                'name' => $this->banjar?->name,
                'address' => $this->banjar?->address,
            ]),
            'address' => $this->address,
            'phone' => $this->phone,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
