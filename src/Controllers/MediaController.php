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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $path = request()->query('path', '/');
        $media = $this->getMediaByPath($path);

        // Ensure the path ends with a '/'
        $path = rtrim($path, '/') . '/';

        [$folders, $files] = $this->separateFoldersAndFiles($media, $path);

        return Response::success(Constants::SUCCESS, [
            'folders' => array_values(array_unique($folders)),
            'files' => $files,
        ]);
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
        $disk = Storage::disk(config('media.storage_disk'));
        $directoryPath = $validated['directory_name'] ?? "/";

        $this->ensureDirectoryExists($disk, $directoryPath);

        $path = $file->store($directoryPath, config('media.storage_disk'));
        $validated['path'] = $path;

        $media = Media::create($validated);

        return Response::success(Constants::SUCCESS, $media);
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
        $disk = Storage::disk(config('media.storage_disk'));

        $this->ensureDirectoryExists($disk, $directoryName);

        return Response::success('Directory created successfully', []);
    }

    /**
     * Retrieve media based on the provided path.
     *
     * @param string $path
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getMediaByPath(string $path)
    {
        return empty($path) 
            ? Media::all() 
            : Media::where('path', 'LIKE', '%' . $path . '%')->get();
    }

    /**
     * Separate media into folders and files based on the current path.
     *
     * @param \Illuminate\Database\Eloquent\Collection $media
     * @param string $path
     * @return array
     */
    private function separateFoldersAndFiles($media, string $path): array
    {
        $folders = [];
        $files = [];

        foreach ($media as $item) {
            $folderPath = dirname($item->path);
            $fileName = basename($item->path);

            if ($folderPath === rtrim($path, '/')) {
                $files[] = [
                    "path"        => $item->path,
                    "title"       => $item->title,
                    "alt"         => $item->alt,
                    "description" => $item->description,
                ];
            } else {
                $this->addFolder($folders, $folderPath, $path);
            }
        }

        return [$folders, $files];
    }

    /**
     * Add folder to the folders array if it is not already present.
     *
     * @param array &$folders
     * @param string $folderPath
     * @param string $currentPath
     */
    private function addFolder(array &$folders, string $folderPath, string $currentPath): void
    {
        $relativePath = str_replace($currentPath, '', $folderPath);
        $folderName = trim(explode('/', $relativePath)[0]);

        if (!empty($folderName) && !in_array($folderName, $folders)) {
            $folders[] = $folderName;
        }
    }

    /**
     * Ensure that the specified directory exists, creating it if necessary.
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem $disk
     * @param string $directoryPath
     */
    private function ensureDirectoryExists($disk, string $directoryPath): void
    {
        if (!$disk->exists($directoryPath)) {
            $disk->makeDirectory($directoryPath, 0777, true);
        }
    }
}