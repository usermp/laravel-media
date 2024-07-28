<?php

namespace Usermp\LaravelMedia\Controllers;

use Illuminate\Routing\Controller;
use Usermp\LaravelMedia\Models\Media;
use Illuminate\Support\Facades\Storage;
use Usermp\LaravelMedia\Services\Response;
use Usermp\LaravelMedia\Requests\DirectoryRequest;
use Usermp\LaravelMedia\Requests\MediaUploadRequest;
use Usermp\LaravelMedia\Services\Constants;

class MediaController extends Controller
{
    /**
     * Display a listing of the media.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Retrieve the 'path' query parameter from the request
        $path = request()->query('path');

        // If no path is provided, return all media records
        if (empty($path)) {
            $media = Media::all();
        } else {
            // Use LIKE to find records that match the provided path
            $media = Media::where('path', 'LIKE', '%' . $path . '%')->get();
        }

        // Separate folders and files
        $folders = [];
        $files = [];

        foreach ($media as $item) {
            $explode = explode("/", $item->path);
            $file = end($explode);
            unset($explode[count($explode) - 1]); // Remove the file name from the path

            // Rebuild the folder path
            $folderPath = implode("/", $explode);

            // Check if the current item is a file or a folder
            if ($folderPath === $path) {
                $files[] = $item; // It's a file in the current path
            } elseif (strpos($folderPath, $path) === 0) {
                // It's a folder under the current path
                $folders[] = $folderPath;
            }
        }

        // Remove duplicates from folders
        $folders = array_unique(array_values($folders));

        // Return the media records as a JSON response
        return response()->json(['media' => ['folders' => $folders, 'files' => $files,]], 200);
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
