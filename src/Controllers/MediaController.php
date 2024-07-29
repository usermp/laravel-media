<?php

namespace Usermp\LaravelMedia\Controllers;

use Illuminate\Routing\Controller;
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
        $mediaItems = $disk->allFiles($path);
        $directories = $disk->allDirectories($path);

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
            $foldersPath = dirname($relativePath);
            if ($foldersPath === '.' || $foldersPath === rtrim($path, '/')) {
                $files[] = $this->formatFileItem($filePath);
            }
        }

        foreach ($mediaItems['folders'] as $folderPath) {
            if ($folderPath !== $path) {
                $this->addFolderToCategory($folders, $folderPath, $path);
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
     * Add folders to the list of folders based on the path.
     *
     * @param array &$folders
     * @param string $folderPath
     * @param string $currentPath
     */
    private function addFolderToCategory(array &$folders, string $folderPath, string $currentPath): void
    {
        if ($currentPath === "/") {
            $folders[] = explode("/", $folderPath)[0];
            return;
        }

        $relativePath = str_replace($currentPath, '', $folderPath);
        $folderParts = explode('/', trim($relativePath, '/'));

        foreach ($folderParts as $folderName) {
            if (!empty($folderName) && !in_array($folderName, $folders)) {
                $folders[] = $folderName;
            }
        }
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
