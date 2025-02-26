<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\Contributor;
use Illuminate\Http\Request;

class NovelController extends Controller
{
    public function index()
    {
        // Menggunakan nested eager loading untuk memuat relasi contributor dan user
        $novels = Novel::with('contributor.user')->get();

        // Mengembalikan respons JSON dengan data relasi
        return response()->json(['novels' => $novels]);
    }

    // Menampilkan detail novel berdasarkan slug
    public function show($slug)
    {
        $novel = Novel::where('slug', $slug)->with('contributor.user')->firstOrFail();
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

    // Menampilkan novel terkait berdasarkan slug
    public function relatedNovels($slug)
    {
        // Temukan novel berdasarkan slug
        $novel = Novel::where('slug', $slug)->firstOrFail();

        // Ekstrak kata-kata dari description novel saat ini
        $keywords = explode(' ', strtolower($novel->description));
        $keywords = array_unique(array_filter($keywords)); // Hapus duplikat dan kata kosong

        // Cari novel terkait berdasarkan kata-kata dalam description
        $relatedNovels = Novel::where('id', '!=', $novel->id) // Jangan tampilkan novel yang sama
            ->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('description', 'LIKE', '%' . $keyword . '%');
                }
            })
            ->with('contributor.user') // Muat relasi contributor dan user
            ->limit(5) // Batasi hasil menjadi 5 novel terkait
            ->get();

        return response()->json(['related_novels' => $relatedNovels]);
    }
}