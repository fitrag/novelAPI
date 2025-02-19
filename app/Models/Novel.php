<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Novel extends Model
{
    protected $fillable = ['contributor_id', 'title', 'description', 'thumbnail', 'slug'];

    public function contributor()
    {
        return $this->belongsTo(Contributor::class);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    // Generate slug otomatis sebelum menyimpan data
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($novel) {
            $novel->slug = Str::slug($novel->title);
        });

        static::updating(function ($novel) {
            if ($novel->isDirty('title')) {
                $novel->slug = Str::slug($novel->title);
            }
        });
    }
}
