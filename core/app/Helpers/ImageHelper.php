<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageHelper
{
    public static function handleUploadedImage($file, $path, $delete = null)
    {
        if ($file) {

            if ($delete) {
                Storage::delete($path . '/' . $delete);
                self::deletePublicMirror($path, $delete);
            }

            $name = Str::random(4) . $file->getClientOriginalName();
            Storage::putFileAs($path, $file, $name);
            self::mirrorUploadedFile($file, $path, $name);

            return $name;
        }
    }


    public static function uploadSummernoteImage($file, $path)
    {

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        if ($file) {

            $name = 'OM_' . time() .  Str::random(8) . '.' . $file->getClientOriginalExtension();
            Storage::putFileAs($path, $file, $name);
            self::mirrorUploadedFile($file, $path, $name);

            return $name;
        }
    }



    public static function ItemhandleUploadedImage($file, $path, $delete = null)
    {
        if ($file) {

            if ($delete) {
                Storage::delete($path . '/' . $delete);
                self::deletePublicMirror($path, $delete);
            }

            $photoName = 'OM_' . time() .  Str::random(8) . '.' . $file->getClientOriginalExtension();
            $thumbnailName = 'OM_' . time() .  Str::random(8) . '.' . $file->getClientOriginalExtension();

            Storage::putFileAs($path, $file, $photoName);
            self::mirrorUploadedFile($file, $path, $photoName);


            $thumbnailPath = $path . '/' . $thumbnailName;
            $thumbnailContents = self::thumbnailContents($file);
            Storage::put($thumbnailPath, $thumbnailContents);
            self::mirrorContents($thumbnailContents, $path, $thumbnailName);


            return [$photoName, $thumbnailName];
        }
    }

    public static function handleUpdatedUploadedImage($file, $path, $data, $delete_path, $field)
    {

        $name = 'OM_' . time() .  Str::random(8) . '.' . $file->getClientOriginalExtension();

        Storage::putFileAs($path, $file, $name);
        self::mirrorUploadedFile($file, $path, $name);


        if ($data[$field] != null) {
            Storage::delete($delete_path . '/' . $data[$field]);
            self::deletePublicMirror($delete_path, $data[$field]);
        }

        return $name;
    }


    public static function ItemhandleUpdatedUploadedImage($file, $path, $data, $delete_path, $field)
    {

        $photoName = 'OM_' . time() .  Str::random(8) . '.' . $file->getClientOriginalExtension();
        $thumbnailName = 'OM_' . time() . Str::random(8) . '.' . $file->getClientOriginalExtension();

        $thumbnailPath = $path . '/' . $thumbnailName;
        $thumbnailContents = self::thumbnailContents($file);
        Storage::put($thumbnailPath, $thumbnailContents);
        self::mirrorContents($thumbnailContents, $path, $thumbnailName);


        $photoPath = $path . '/' . $photoName;
        Storage::putFileAs($path, $file, $photoName);
        self::mirrorUploadedFile($file, $path, $photoName);

        if (!empty($data['thumbnail'])) {
            Storage::delete($delete_path . '/' . $data['thumbnail']);
            self::deletePublicMirror($delete_path, $data['thumbnail']);
        }

        if (!empty($data[$field])) {
            Storage::delete($delete_path . '/' . $data[$field]);
            self::deletePublicMirror($delete_path, $data[$field]);
        }

        return [$photoName, $thumbnailName];
    }


    public static function handleDeletedImage($data, $field, $delete_path)
    {
        if (!empty($data[$field])) {
            Storage::delete($delete_path . '/' . $data[$field]);
            self::deletePublicMirror($delete_path, $data[$field]);
        }
    }

    private static function mirrorUploadedFile($file, $path, $name)
    {
        if (!$file || !$file->isValid()) {
            return;
        }

        $destination = self::publicMirrorPath($path, $name);
        File::ensureDirectoryExists(dirname($destination));
        File::copy($file->getRealPath(), $destination);
    }

    public static function thumbnailContents($file)
    {
        try {
            return (string) \Image::make($file)->resize(230, 230)->encode();
        } catch (\Throwable $exception) {
            return file_get_contents($file->getRealPath());
        }
    }

    private static function mirrorContents($contents, $path, $name)
    {
        $destination = self::publicMirrorPath($path, $name);
        File::ensureDirectoryExists(dirname($destination));
        File::put($destination, $contents);
    }

    public static function deletePublicMirror($path, $name)
    {
        $file = self::publicMirrorPath($path, $name);

        if (!File::exists($file)) {
            return true;
        }

        $publicStorage = public_path('storage');
        $storageAppPublic = storage_path('app/public');

        if (is_link($publicStorage) && realpath($publicStorage) === realpath($storageAppPublic)) {
            return true;
        }

        return File::delete($file);
    }

    public static function publicMirrorPath($path, $name)
    {
        return public_path('storage/' . trim($path, '/') . '/' . ltrim($name, '/'));
    }
}
