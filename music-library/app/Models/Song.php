<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Song extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'duration', 'genre', 'release_year', 'album_id'];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }
}
