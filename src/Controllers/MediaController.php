<?php

namespace Usermp\LaravelMedia\Controllers;

use Illuminate\Routing\Controller;
use Usermp\LaravelMedia\Models\Media;
use Usermp\LaravelMedia\Requests\DirectoryRequest;
use Usermp\LaravelMedia\Requests\MediaUploadRequest;

class MediaController extends Controller
{
    public function index()
    {
        return Media::find(1);
    }
    public function upload(MediaUploadRequest $request)
    {
        $validated = $request->validated();

        $file = $request->file('media');

        $path = $file->storeAs('media', $file->getClientOriginalName(), config('laravelMedia.storage_disk') . $validated['directory_name']);

        $validated['path'] = $path;

        $media = Media::create($validated);

        return response()->json(['media' => $media], 201);
    }

    public function directory(DirectoryRequest $request)
    {
        $validated = $request->validated();

        $directoryName = $validated['directory_name'];

        $directoryPath = config('laravelMedia.storage_disk') . $directoryName;

        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, 0777, true);
        }

        return response()->json(['message' => 'Directory created successfully'], 201);
    }
}
