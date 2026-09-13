<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaItem extends Model
{
    protected $fillable = ['external_id', 'url', 'name', 'alt', 'size', 'mime'];
}
