<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Products extends Model
{
    use SoftDeletes;
    //
    protected $fillable = ['name', 'image_url', 'description', 'price_buy', 'price_sell', 'expiration_date', 'stock', 'min_stock', 'des_stock', 'barcode', 'category_id', 'brand_id', 'supplier_id', 'created_by', 'updated_by'];

    public function category()
    {
        return $this->belongsTo(Categories::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brands::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Suppliers::class);
    }
}
