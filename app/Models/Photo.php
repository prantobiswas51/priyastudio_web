<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photo extends Model
{
    protected $fillable = [
        'name',
        'url',
        'description',
        'type',
        'album_id',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }
}
