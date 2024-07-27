<?php

namespace Usermp\LaravelMedia\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MediaUploadRequest extends FormRequest
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
            'media'          => 'required|file|max:10000000',
            'directory_name' => 'required|string',
            'title'          => 'nullable|string|max:255',
            'alt'            => 'nullable|string|max:255',
            'description'    => 'nullable|string',
        ];
    }
}
