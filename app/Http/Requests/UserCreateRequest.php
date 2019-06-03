<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'  =>  'required|alpha',
            'surname'   =>  'required|alpha',
            'email' =>  'required|email',
            'password'  =>  'required'
        ];
    }

    public function messages() {
        return [
            'name.required' => 'El :attribute es obligatorio.',
            'surname.required' => 'El :attribute es obligatorio.',
            'email.required' => 'El :attribute es obligatorio.',
            'password.required' => 'La :attribute es obligatoria.',

        ];
    }

    public function atributes() {
        return [
            'name'      =>  'nombre del usuario',
            'surname'   =>  'apellido del usuario',
            'email'     =>  'correo electrónico',
            'password'  =>  'contraseña'
        ];
    }

    public function response(array $errors) {
        if ($this->expectsJson()) {
            return new JsonResponse($errors, 422);
        }

        return $this->redirector->to($this->getRedirectUrl())
            ->withInput($this->except($this->dontFlash))
            ->withErrors($errors, $this->errorBag);
    }
}
