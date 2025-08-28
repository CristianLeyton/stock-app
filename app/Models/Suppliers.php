<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Suppliers extends Model
{
    use SoftDeletes;
    //
    protected $fillable = ['name', 'contact_name', 'contact_phone', 'contact_email', 'address', 'image', 'notes', 'description', 'created_by', 'updated_by'];

    public function products()
    {
        return $this->hasMany(Products::class);
    }
}
