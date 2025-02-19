<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\Contributor;
use Illuminate\Http\Request;

class NovelController extends Controller
{
    // Menampilkan semua novel
    public function index()
    {
        $novels = Novel::all();
        return response()->json(['novels' => $novels]);
    }

    // Menampilkan detail novel berdasarkan slug
    public function show($slug)
    {
        $novel = Novel::where('slug', $slug)->firstOrFail();
        return response()->json(['novel' => $novel]);
    }

    // Menambahkan novel baru
    public function store(Request $request)
    {
        $contributor = $request->user()->contributor;

        if (!$contributor) {
            return response()->json(['message' => 'You must be a contributor to create a novel'], 403);
        }

        $novel = new Novel();
        $novel->contributor_id = $contributor->id;
        $novel->title = $request->input('title');
        $novel->description = $request->input('description');
        $novel->thumbnail = $request->input('thumbnail'); // Simpan path gambar
        $novel->save(); // Slug akan di-generate otomatis

        return response()->json(['message' => 'Novel created', 'novel' => $novel], 201);
    }

    // Mengupdate novel
    public function update(Request $request, $id)
    {
        $novel = Novel::findOrFail($id);

        if ($novel->contributor->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $novel->title = $request->input('title', $novel->title);
        $novel->description = $request->input('description', $novel->description);
        $novel->thumbnail = $request->input('thumbnail', $novel->thumbnail);
        $novel->save(); // Slug akan di-update jika title berubah

        return response()->json(['message' => 'Novel updated', 'novel' => $novel]);
    }

    // Menghapus novel
    public function destroy(Request $request, $id)
    {
        $novel = Novel::findOrFail($id);

        if ($novel->contributor->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $novel->delete();

        return response()->json(['message' => 'Novel deleted']);
    }
}