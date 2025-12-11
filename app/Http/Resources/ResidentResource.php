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
            'user_id' => $this->user_id,
            'created_by_user_id' => $this->created_by_user_id,
            'family_card_number' => $this->family_card_number,
            'name' => $this->name,
            'gender' => $this->gender,
            'place_of_birth' => $this->place_of_birth,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'family_status' => $this->family_status,
            'religion' => $this->religion,
            'education' => $this->education,
            'work_type' => $this->work_type,
            'marital_status' => $this->marital_status,
            'origin_address' => $this->origin_address,
            'residential_address' => $this->residential_address,
            'rt_number' => $this->rt_number,
            'residence_name' => $this->residence_name,
            'house_number' => $this->house_number,
            'location' => $this->location,
            'arrival_date' => $this->arrival_date?->format('Y-m-d'),
            'phone' => $this->phone,
            'email' => $this->email,
            'validation_status' => $this->validation_status,
            'rejection_reason' => $this->rejection_reason,
            'photo_house' => $this->photo_house ? asset('storage/' . $this->photo_house) : null,
            'resident_photo' => $this->resident_photo ? asset('storage/' . $this->resident_photo) : null,
            'photo_ktp' => $this->photo_ktp ? asset('storage/' . $this->photo_ktp) : null,
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
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
