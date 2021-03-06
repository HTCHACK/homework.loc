<?php

namespace App\Models\Product;

use App\Models\Material\Material;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product\ProductMaterial;
use App\Models\User;
use App\Models\WareHouseMaterial\WareHouse;
use DateTimeInterface;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $guarded = [''];


    public function materials()
    {
    return $this->belongsToMany(Material::class,'product_material', 'product_id', 'material_id')->withPivot('quantity')->withTimestamps();
    }

    public function productMaterials()
    {
        return $this->hasMany(ProductMaterial::class);
    }

    public function warehouses()
    {
        return $this->hasMany(WareHouse::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

}
