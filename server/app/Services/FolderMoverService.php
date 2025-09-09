<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache; // Ou Redis, ou DB selon besoins

class FolderMoverService
{
    public static function moveWithProgress(string $source, string $destination, string $id)
    {
        $files = collect(\File::allFiles($source));
        $total = $files->count();
        $copied = 0;

        foreach ($files as $file) {
            $destPath = $destination . '/' . $file->getRelativePathname();
            \File::ensureDirectoryExists(dirname($destPath));
            \File::copy($file->getPathname(), $destPath);
            $copied++;

            $percent = intval(($copied / $total) * 100);
            Cache::put("progress:$id", $percent);
        }
        Cache::put("progress:$id", 100);
    }

    public static function getProgress(string $id)
    {
        return Cache::get("progress:$id", 0);
    }

    public static function removeProgress(string $id)
    {
        Cache::forget("progress:$id");
    }
}
