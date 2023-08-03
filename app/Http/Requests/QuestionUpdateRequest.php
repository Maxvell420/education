<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionUpdateRequest extends FormRequest
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
        $rules=[];
        if ($this->file != null) {
            $rules["file"]=["image"];
        }
        if ($this->title != null) {
            $rules["title"]=["min:3","max:100","unique:questions"];
        }
        if ($this->problem != null) {
            $rules["problem"]=["min:3","max:100","unique:questions"];
        }
        if ($this->correct_answer !=null){
            $rules["correct_answer"]=["min:3","max:100"];
        }
        if ($this->incorrect_answer_1 != null){
            $rules["incorrect_answer_1"]=["min:3","max:100"];
        }
        if ($this->incorrect_answer_2 != null){
            $rules["incorrect_answer_2"]=["min:3","max:100"];
        }
        if ($this->incorrect_answer_3 != null){
            $rules["incorrect_answer_3"]=["min:3","max:100"];
        }
        return $rules;
    }
}
