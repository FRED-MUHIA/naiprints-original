<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaAssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $media = MediaAsset::orderByDesc('created_at')->paginate(18);

        return view('admin.media.index', compact('media'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4', 'max:20480'],
            'title' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);

        $path = $request->file('file')->store('media', 'public');

        MediaAsset::create([
            'file_path' => $path,
            'title' => $data['title'] ?? null,
            'alt_text' => $data['alt_text'] ?? null,
        ]);

        return redirect()->route('admin.media.index')
            ->with('success', 'Media uploaded.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MediaAsset $medium)
    {
        $data = $request->validate([
            'file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4', 'max:20480'],
            'title' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($medium->file_path);
            $data['file_path'] = $request->file('file')->store('media', 'public');
        }

        $medium->update([
            'file_path' => $data['file_path'] ?? $medium->file_path,
            'title' => $data['title'] ?? $medium->title,
            'alt_text' => $data['alt_text'] ?? $medium->alt_text,
        ]);

        return redirect()->route('admin.media.index')
            ->with('success', 'Media updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MediaAsset $medium)
    {
        Storage::disk('public')->delete($medium->file_path);
        $medium->delete();

        return redirect()->route('admin.media.index')
            ->with('success', 'Media deleted.');
    }
}
