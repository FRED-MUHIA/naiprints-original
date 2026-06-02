<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class JerseyLogoController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'logo' => ['required', 'file', 'mimes:png,jpg,jpeg,webp', 'max:6144'],
        ]);

        $disk = $this->resolveDisk();
        $path = $request->file('logo')->storePublicly('jersey-logos', $disk);
        $url = Storage::disk($disk)->url($path);

        return response()->json([
            'message' => 'Logo uploaded successfully.',
            'data' => [
                'disk' => $disk,
                'path' => $path,
                'url' => $url,
            ],
        ]);
    }

    private function resolveDisk(): string
    {
        $configured = (string) config('jersey.storage_disk', 'public');
        if (config("filesystems.disks.{$configured}")) {
            return $configured;
        }

        if (config('filesystems.disks.cloudinary') && env('CLOUDINARY_URL')) {
            return 'cloudinary';
        }

        if (config('filesystems.disks.s3')) {
            return 's3';
        }

        return 'public';
    }
}
