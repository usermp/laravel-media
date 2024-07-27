<?php

namespace Usermp\LaravelMedia\Controllers;

use Illuminate\Routing\Controller;
use Usermp\LaravelMedia\Models\Media;
use Illuminate\Support\Facades\Storage;
use Usermp\LaravelMedia\Requests\DirectoryRequest;
use Usermp\LaravelMedia\Requests\MediaUploadRequest;

class MediaController extends Controller
{
    /**
     * Display a listing of the media.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Instead of returning a single media item, return a collection of all media
        return Media::all();
    }

    /**
     * Handle media upload request.
     *
     * @param  \Usermp\LaravelMedia\Requests\MediaUploadRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(MediaUploadRequest $request)
    {
        $validated = $request->validated();

        $file = $request->file('media');
        // Get the disk instance
        $disk = Storage::disk(config('media.storage_disk'));

        // Build the directory path
        $directoryPath = $validated['directory_name'];

        // Check if the directory exists, and create it if not
        if (!$disk->exists($directoryPath)) {
            $disk->makeDirectory($directoryPath, 0777, true);
        }


        // Use the `store` method to generate a unique filename
        $path = $file->store($directoryPath, config('media.storage_disk'));

        $validated['path'] = $path;

        $media = Media::create($validated);

        return response()->json(['media' => $media], 201);
    }

    /**
     * Handle directory creation request.
     *
     * @param  \Usermp\LaravelMedia\Requests\DirectoryRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function directory(DirectoryRequest $request)
    {
        $validated = $request->validated();

        $directoryName = $validated['directory_name'];

        // Get the disk instance
        $disk = Storage::disk(config('media.storage_disk'));

        // Check if the directory exists, and create it if not
        if (!$disk->exists($directoryName)) {
            $disk->makeDirectory($directoryName, 0777, true);
        }

        return response()->json(['message' => 'Directory created successfully'], 201);
    }
}
