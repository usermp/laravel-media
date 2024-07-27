<?php

namespace Usermp\LaravelMedia\Controllers;

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

        $validated['path'] = $path;

        $media = Media::create($validated);

        return response()->json(['media' => $media], 201);
    }
}
