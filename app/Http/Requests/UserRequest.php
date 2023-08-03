<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules=[
            "name"=>"required|min:3|max:255",
            "email"=>["required"],
            "password"=>"required|min:3|max:15"
        ];
        if (!empty($this->user)){
            $rules["email"][]=Rule::unique("users")->ignore($this->user->id);
        }
        else $rules["email"][]=Rule::unique("users");
        return $rules;
    }
}
