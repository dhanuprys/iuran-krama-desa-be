<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResidentRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get the resident ID from the route parameter to ignore unique check
        // The parameter name depends on the route definition, typically 'resident'
        $id = $this->route('resident');

        return [
            'nik' => 'sometimes|string|max:16|unique:residents,nik,' . $id,
            'user_id' => 'sometimes|exists:users,id',
            'family_card_number' => 'sometimes|string|max:16',
            'name' => 'sometimes|string|max:80',
            'gender' => 'sometimes|in:L,P',
            'place_of_birth' => 'sometimes|string|max:50',
            'date_of_birth' => 'sometimes|date',
            'family_status' => 'sometimes|in:HEAD_OF_FAMILY,PARENT,HUSBAND,WIFE,CHILD',
            'banjar_id' => 'required_if:family_status,HEAD_OF_FAMILY|nullable|exists:banjars,id',
            'religion' => 'required_if:family_status,HEAD_OF_FAMILY|nullable|string|max:50',
            'education' => 'required_if:family_status,HEAD_OF_FAMILY|nullable|string|max:50',
            'work_type' => 'required_if:family_status,HEAD_OF_FAMILY|nullable|string|max:50',
            'marital_status' => 'required_if:family_status,HEAD_OF_FAMILY|nullable|in:MARRIED,SINGLE,DEAD_DIVORCE,LIVING_DIVORCE',
            'origin_address' => 'required_if:family_status,HEAD_OF_FAMILY|nullable|string',
            'residential_address' => 'required_if:family_status,HEAD_OF_FAMILY|nullable|string',
            'rt_number' => 'required_if:family_status,HEAD_OF_FAMILY|nullable|string|max:10',
            'residence_name' => 'required_if:family_status,HEAD_OF_FAMILY|nullable|string|max:100',
            'house_number' => 'required_if:family_status,HEAD_OF_FAMILY|nullable|string|max:20',
            'location' => 'nullable|array',
            'arrival_date' => 'nullable|date',
            'phone' => 'required_if:family_status,HEAD_OF_FAMILY|nullable|string|max:12',
            'email' => 'required_if:family_status,HEAD_OF_FAMILY|nullable|email|max:50',
            'validation_status' => 'nullable|in:PENDING,APPROVED,REJECTED',
            'photo_house' => 'required_if:family_status,HEAD_OF_FAMILY|nullable|image|max:5120',
            'resident_photo' => 'required_if:family_status,HEAD_OF_FAMILY|nullable|image|max:5120',
            'photo_ktp' => 'required_if:family_status,HEAD_OF_FAMILY|nullable|image|max:5120',
            'resident_status_id' => 'required_if:family_status,HEAD_OF_FAMILY|nullable|exists:resident_statuses,id',
        ];
    }
}
