<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'bio'];

    public function albums(): HasMany
    {
        return $this->hasMany(Album::class);
    }

    public function songs(): HasManyThrough
    {
        return $this->hasManyThrough(Song::class, Album::class);
    }
}
