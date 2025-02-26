<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\NovelController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\ContributorController;

use App\Http\Controllers\AuthController;

// Grup untuk autentikasi
Route::group(['prefix' => 'auth'], function () {
    // Register user baru
    Route::post('/register', [AuthController::class, 'register']);

    // Login user
    Route::post('/login', [AuthController::class, 'login']);

    // Logout user (memerlukan autentikasi)
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});

// Grup untuk endpoint umum (tanpa autentikasi)
Route::group(['prefix' => 'public'], function () {
    // Lihat semua novel
    Route::get('/novels', [NovelController::class, 'index']);

    // Lihat detail novel berdasarkan slug
    Route::get('/novels/{slug}', [NovelController::class, 'show']);

    // Lihat semua chapter dari sebuah novel
    Route::get('/novels/{novelSlug}/chapters', [ChapterController::class, 'index']);

    // Lihat novel terkait berdasarkan slug
    Route::get('/novels/{slug}/related', [NovelController::class, 'relatedNovels']);

    // Lihat detail chapter berdasarkan slug
    Route::get('/chapters/{slug}', [ChapterController::class, 'show']);
});

// Grup untuk endpoint yang memerlukan autentikasi
Route::middleware('auth:sanctum')->group(function () {
    // Mendaftarkan user sebagai contributor
    Route::post('/register-contributor', [ContributorController::class, 'registerAsContributor']);

    // Menambahkan novel baru
    Route::post('/novels', [NovelController::class, 'store']);

    // Menambahkan chapter baru ke novel tertentu
    Route::post('/novels/{novelId}/chapters', [ChapterController::class, 'store']);

    // Mengupdate novel
    Route::put('/novels/{id}', [NovelController::class, 'update']);

    // Menghapus novel
    Route::delete('/novels/{id}', [NovelController::class, 'destroy']);

    // Mengupdate chapter
    Route::put('/chapters/{id}', [ChapterController::class, 'update']);

    // Menghapus chapter
    Route::delete('/chapters/{id}', [ChapterController::class, 'destroy']);
});

