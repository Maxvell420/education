<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuestionRequest extends FormRequest
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
        "title"=>["required","unique:questions","min:3","max:30"],
        "problem"=>["required","unique:questions","min:3","max:255"],
        "correct_answer"=>["required","min:3","max:255"],
        "question_type"=>["required"],
        "file"=>["nullable","image"]
    ];
        if ($this->question_type=="test"){
            $rules["incorrect_answer_1"]=["required","min:3","max:100"];
            $rules["incorrect_answer_2"]=["required","min:3","max:100"];
            $rules["incorrect_answer_3"]=["required","min:3","max:100"];
        }
        if ($this->question_type=="writing"){
            $rules["incorrect_answer_1"]=["exclude"];
            $rules["incorrect_answer_2"]=["exclude"];
            $rules["incorrect_answer_3"]=["exclude"];
        }
        return $rules;
    }
}
