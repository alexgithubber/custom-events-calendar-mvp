<?php

namespace App\Http\Requests;

class EventUpdateRequest extends AbstractApiRequest
{
    public function rules(): array
    {
        return [
            'location' => [
                'string',
                'max:250',
                'required_without_all:date,invitees',
            ],
            'date' => 'date_format:Y-m-d H:i|required_without_all:location,invitees',
            'invitees' => 'array|required_without_all:location,date',
        ];
    }

    public function messages(): array
    {
        return [
            'location.required_without_all' => 'At least one field is required for update (location, date or invitees)',
            'date.required_without_all' => 'At least one field is required for update (location, date or invitees)',
            'invitees.required_without_all' => 'At least one field is required for update (location, date or invitees)',
        ];
    }
}
