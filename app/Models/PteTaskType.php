<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PteTaskType extends Model
{
    protected $fillable = ['slug', 'label', 'short', 'color'];
}
