<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Novel;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    // Menampilkan semua chapter dari novel tertentu
    public function index($novelSlug)
    {
        $novel = Novel::where('slug', $novelSlug)->firstOrFail();
        $chapters = $novel->chapters;
        return response()->json(['chapters' => $chapters]);
    }

    // Menampilkan detail chapter berdasarkan slug
    public function show($slug)
    {
        // Temukan chapter berdasarkan slug dan muat relasi novel beserta contributor
        $chapter = Chapter::where('slug', $slug)->with(['novel.contributor.user'])->firstOrFail();

        // Cari chapter sebelumnya (prev) berdasarkan order
        $prevChapter = Chapter::where('novel_id', $chapter->novel_id)
            ->where('order', '<', $chapter->order)
            ->orderBy('order', 'desc')
            ->first();

        // Cari chapter berikutnya (next) berdasarkan order
        $nextChapter = Chapter::where('novel_id', $chapter->novel_id)
            ->where('order', '>', $chapter->order)
            ->orderBy('order', 'asc')
            ->first();

        return response()->json([
            'chapter' => $chapter,
            'novel' => $chapter->novel, // Sertakan data novel secara eksplisit
            'pagination' => [
                'prev' => $prevChapter ? [
                    'id' => $prevChapter->id,
                    'title' => $prevChapter->title,
                    'slug' => $prevChapter->slug,
                ] : null,
                'next' => $nextChapter ? [
                    'id' => $nextChapter->id,
                    'title' => $nextChapter->title,
                    'slug' => $nextChapter->slug,
                ] : null,
            ],
        ]);
    }

    // Menambahkan chapter baru ke novel tertentu
    public function store(Request $request, $novelId)
    {
        $novel = Novel::findOrFail($novelId);

        if ($novel->contributor->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $chapter = new Chapter();
        $chapter->novel_id = $novel->id;
        $chapter->title = $request->input('title');
        $chapter->content = $request->input('content');
        $chapter->image = $request->input('image'); // Gambar opsional
        $chapter->order = $request->input('order');
        $chapter->save(); // Slug akan di-generate otomatis

        return response()->json(['message' => 'Chapter created', 'chapter' => $chapter], 201);
    }

    // Mengupdate chapter
    public function update(Request $request, $id)
    {
        $chapter = Chapter::findOrFail($id);

        if ($chapter->novel->contributor->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $chapter->title = $request->input('title', $chapter->title);
        $chapter->content = $request->input('content', $chapter->content);
        $chapter->image = $request->input('image', $chapter->image);
        $chapter->order = $request->input('order', $chapter->order);
        $chapter->save(); // Slug akan di-update jika title berubah

        return response()->json(['message' => 'Chapter updated', 'chapter' => $chapter]);
    }

    // Menghapus chapter
    public function destroy(Request $request, $id)
    {
        $chapter = Chapter::findOrFail($id);

        if ($chapter->novel->contributor->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $chapter->delete();

        return response()->json(['message' => 'Chapter deleted']);
    }
}