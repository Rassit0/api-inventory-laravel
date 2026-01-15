<?php

namespace App\Http\Requests\User;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    protected function prepareForValidation()
    {
        $this->merge([
            'surname'       => $this->surname === '__NULL__' ? null : $this->surname,
            'phone'         => $this->phone === '__NULL__' ? null : $this->phone,
            'type_document' => $this->type_document === '__NULL__' ? null : $this->type_document,
            'n_document'    => $this->n_document === '__NULL__' ? null : $this->n_document,
            'branch_id'     => $this->branch_id === '__NULL__' ? null : $this->branch_id,
        ]);
    }
    public function rules(): array
    {
        $userId = $this->route('id'); // ⚡ ID del usuario en la ruta (ej: /users/{user})

        $rules = [
            'name'          => [
            'required',
            'string',
            'max:255',
            Rule::unique('users', 'name')->ignore($userId),
            ],
            'surname'       => 'nullable|string|max:255',
            'email'         => [
            'required',
            'email',
            Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => !$userId
            ? 'required|string|min:6|confirmed'
            : 'nullable|string|min:6|confirmed',
            'phone'         => 'nullable|string|max:50',
            'type_document' => 'nullable|string|max:50',
            'n_document'    => [
            'nullable',
            'string',
            'max:50',
            Rule::unique('users', 'n_document')->ignore($userId),
            ],
            'gender'        => 'nullable|in:male,female,other',
            'role_id'       => 'required|exists:roles,id',
            'branch_id'     => 'nullable|exists:branches,id',
            'imagen'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'state'         => 'nullable|in:0,1',
        ];

        return $rules;
    }

    /**
     * Mensajes de validación personalizados.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'name.unique' => 'Ya existe un usuario con este nombre.',

            'surname.max' => 'El apellido no puede tener más de 255 caracteres.',

            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe ingresar un correo electrónico válido.',
            'email.unique' => 'Este correo ya está registrado.',

            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',

            'phone.max' => 'El número de teléfono no puede superar los 50 caracteres.',

            'type_document.max' => 'El tipo de documento no puede superar los 50 caracteres.',

            'n_document.max' => 'El número de documento no puede superar los 50 caracteres.',
            'n_document.unique' => 'Este número de documento ya está registrado.',

            'gender.in' => 'El género seleccionado no es válido.',

            'role_id.required' => 'El rol es obligatorio.',
            'role_id.exists' => 'El rol seleccionado no existe.',

            'branch_id.exists' => 'La sucursal seleccionada no existe.',

            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser de tipo JPG, JPEG o PNG.',
            'imagen.max' => 'La imagen no debe superar los 2 MB.',
            
            'state.in' => 'El estado debe ser 1 (activo) o 0 (inactivo).',
        ];
    }
}
