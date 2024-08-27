<?php

namespace Usermp\LaravelMedia\Controllers;

use Illuminate\Routing\Controller;
use Usermp\LaravelMedia\Models\Media;
use Illuminate\Support\Facades\Storage;
use Usermp\LaravelMedia\Services\Response;
use Usermp\LaravelMedia\Services\Constants;
use Usermp\LaravelMedia\Requests\DirectoryRequest;
use Usermp\LaravelMedia\Requests\MediaUploadRequest;

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
        $disk = Storage::disk(config('media.storage_disk'));

        // Ensure the path ends with a '/'
        $path = rtrim($path, '/') . '/';

        $mediaItems = $this->fetchMediaFromStorage($disk, $path);
        [$folders, $files] = $this->categorizeMedia($mediaItems, $path);

        return Response::success(Constants::SUCCESS, [
            'folders' => array_values(array_unique($folders)),
            'files' => $files,
        ]);
    }

    /**
     * Handle media upload request.
     *
     * @param \Usermp\LaravelMedia\Requests\MediaUploadRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(MediaUploadRequest $request)
    {
        $validated = $request->validated();
        $file = $request->file('media');
        $disk = Storage::disk(config('media.storage_disk'));
        $directoryPath = $validated['directory_name'] ?? "/";

        $this->createDirectoryIfNotExists($disk, $directoryPath);

        $path = $file->store($directoryPath, config('media.storage_disk'));
        $validated['path'] = $path;
        
        unset($validated['media']);
        Media::create(
            $validated
        );

        return Response::success(Constants::SUCCESS, [
            'path' => $path,
            'directory' => $directoryPath,
        ]);
    }

    /**
     * Handle directory creation request.
     *
     * @param \Usermp\LaravelMedia\Requests\DirectoryRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function directory(DirectoryRequest $request)
    {
        $validated = $request->validated();
        $directoryName = $validated['directory_name'];
        $disk = Storage::disk(config('media.storage_disk'));

        $this->createDirectoryIfNotExists($disk, $directoryName);

        return Response::success('Directory created successfully', []);
    }

    /**
     * Fetch media files and directories from storage based on the provided path.
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem $disk
     * @param string $path
     * @return array
     */
    private function fetchMediaFromStorage($disk, string $path): array
    {
        $mediaItems = $disk->files($path); // Changed from allFiles to files
        $directories = $disk->directories($path); // Changed from allDirectories to directories

        return [
            'files' => $mediaItems,
            'folders' => $directories
        ];
    }

    /**
     * Categorize media items into folders and files based on the current path.
     *
     * @param array $mediaItems
     * @param string $path
     * @return array
     */
    private function categorizeMedia(array $mediaItems, string $path): array
    {
        $folders = [];
        $files = [];

        foreach ($mediaItems['files'] as $filePath) {
            $relativePath = str_replace($path, '', $filePath);
            if ($relativePath === basename($relativePath)) {
                // Only add if it is directly under the current path
                $files[] = $this->formatFileItem($filePath);
            }
        }

        foreach ($mediaItems['folders'] as $folderPath) {
            // Only add if it is directly under the current path
            if (basename($folderPath) !== '') {
                $folders[] = basename($folderPath);
            }
        }

        return [$folders, $files];
    }

    /**
     * Format a file item for response.
     *
     * @param string $filePath
     * @return array
     */
    private function formatFileItem(string $filePath): array
    {
        return [
            'path' => $filePath,
            'title' => basename($filePath),
            'alt' => '', // Example placeholder
            'description' => '', // Example placeholder
        ];
    }

    /**
     * Ensure that the specified directory exists, creating it if necessary.
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem $disk
     * @param string $directoryPath
     */
    private function createDirectoryIfNotExists($disk, string $directoryPath): void
    {
        if (!$disk->exists($directoryPath)) {
            $disk->makeDirectory($directoryPath, 0777, true);
        }
    }
}
