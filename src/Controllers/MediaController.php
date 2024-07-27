<?php

namespace MyVendor\LaravelMedia\Controllers;

use Usermp\LaravelMedia\Requests\MediaUploadRequest;
use Usermp\LaravelMedia\Models\Media;
use Illuminate\Routing\Controller;

class MediaController extends Controller
{
    public function upload(MediaUploadRequest $request)
    {
        $validated = $request->validated();

        $file = $request->file('media');
        $path = $file->storeAs('media', $file->getClientOriginalName(), config('laravelMedia.storage_disk'));

        $media = Media::create([
            'title' => $request->title,
            'alt' => $request->alt,
            'user_id' => $request->user_id,
            'description' => $request->description,
            'path' => $path,
        ]);

        return response()->json(['media' => $media], 201);
    }
}
