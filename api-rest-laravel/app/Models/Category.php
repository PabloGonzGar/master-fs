<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    // Relacion 1-n
    public function posts() {
        return $this->hasMany('App\Models\Post');
    }
}
