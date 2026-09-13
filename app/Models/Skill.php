<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['slug', 'label', 'color', 'soft', 'icon', 'sort_order'];
}
