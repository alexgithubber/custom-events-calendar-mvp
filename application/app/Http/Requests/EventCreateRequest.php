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
//            'email' => [
//                'email',
//                'max:250',
//                'required',
//                'unique:subscribers,email',
//            ],
            'invitees' => 'array|required',
//            'fields.*.title' => 'required|exists:fields,title',
//            'fields.*.value' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'location.required' => 'location is required',
//            'email.required' => 'email is required',
//            'email.email' => 'invalid email informed',
//            'email.unique' => 'email informed already exists',
            'invitees.required' => 'invitees cannot be empty',
//            'fields.array' => 'fields must be an array of objects',
//            'fields.*.title.required' => 'field.title is required',
//            'fields.*.value.required' => 'fields.value is required',
//            'fields.*.title.exists' => 'a field with the title \':input\' could not be found',
        ];
    }
}
