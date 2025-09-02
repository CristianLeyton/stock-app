<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brands extends Model
{
    use SoftDeletes;
    //
    protected $fillable = ['name', 'image', 'description', 'created_by', 'updated_by'];

    public function products()
    {
        return $this->hasMany(Products::class);
    }

        public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
