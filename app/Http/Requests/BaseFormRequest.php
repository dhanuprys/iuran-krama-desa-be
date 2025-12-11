<?php

namespace App\Http\Requests;

use App\Helpers\ApiError;
use App\Helpers\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseFormRequest extends FormRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $errorDef = ApiError::get('VALIDATION_ERROR');

        $response = ResponseHelper::error(
            $errorDef['code'],
            $errorDef['message'],
            $validator->errors() // Details
        );

        throw new HttpResponseException(response()->json($response, 422));
    }
}
