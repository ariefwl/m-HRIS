<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'check_type' => 'required|in:IN,OUT',
            'latitude'   => 'required|numeric|between:-90,90',
            'longitude'  => 'required|numeric|between:-180,180',
            'accuracy'   => 'nullable|integer|min:0',
            'is_mock'    => 'required|boolean'

            // 'note' => [
            //     Rule::requiredIf(fn () => $this->check_type === 'OUT'),
            //     'string',
            //     'max:255'
            // ],

            // 'photo' => [
            //     Rule::requiredIf(fn () => $this->check_type === 'OUT'),
            //     'image',
            //     'max:2048'
            // ],
        ];
    }

    public function messages(): array
    {
        return [
            'check_type.required' => 'Tipe absensi wajib diisi',
            'check_type.in'       => 'Tipe absensi hanya IN atau OUT',
            'latitude.required'  => 'Latitude wajib diisi',
            'longitude.required' => 'Longitude wajib diisi',
        ];
    }
}
