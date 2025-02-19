<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Chapter extends Model
{
    protected $fillable = ['novel_id', 'title', 'content', 'image', 'order', 'slug'];

    public function novel()
    {
        return $this->belongsTo(Novel::class);
    }

    // Generate slug otomatis sebelum menyimpan data
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($chapter) {
            $chapter->slug = Str::slug($chapter->title);
        });

        static::updating(function ($chapter) {
            if ($chapter->isDirty('title')) {
                $chapter->slug = Str::slug($chapter->title);
            }
        });
    }
}