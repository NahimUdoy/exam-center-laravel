<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MockSection extends Model
{
    protected $fillable = ['slug', 'label', 'color', 'soft', 'band'];
}
