<?php

namespace App\Http\Requests;

class EventCreateRequest extends AbstractApiRequest
{
    public function rules(): array
    {
        return [
            'location' => [
                'string',
                'max:250',
                'required',
            ],
            'date' => 'required|date_format:Y-m-d H:i',
            'invitees' => 'array|required',
        ];
    }

    public function messages(): array
    {
        return [
            'location.required' => 'location is required',
            'date.required' => 'location is required',
            'invitees.required' => 'invitees cannot be empty',
        ];
    }
}
