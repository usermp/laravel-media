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
            'media'        => 'required|file|max:' . config('media.max_upload_size'),
            'title'        => 'nullable|string|max:255',
            'alt'          => 'nullable|string|max:255',
            'description'  => 'nullable|string',
        ];
    }
}
