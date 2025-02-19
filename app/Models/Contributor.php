<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contributor extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function novels()
    {
        return $this->hasMany(Novel::class);
    }
}