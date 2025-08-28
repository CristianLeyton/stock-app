<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categories extends Model
{
    use SoftDeletes;
    //
    protected $fillable = ['name', 'image', 'description', 'created_by', 'updated_by'];

    public function products()
    {
        return $this->hasMany(Products::class);
    }
}
