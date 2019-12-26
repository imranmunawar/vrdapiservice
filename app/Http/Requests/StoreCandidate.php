<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCandidate extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'     => 'required',
            'email'    => 'required|email',
            'password' => 'required',
            'phone'    => 'required'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Please Enter Email',
            'email.email'    => 'Please Enter Valid Email',
            'name.required'  => 'Please Enter Name',
            'password.required' => 'Please Enter Password',
            'phone.required' => 'Please Enter Phone Number',
        ];
    }
}
