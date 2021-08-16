<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Material\Material;
use App\Models\Product\Product;
use DateTimeInterface;
use App\Models\WareHouseMaterial\WareHouse;
use App\Models\WareHouseMaterial\WareHouseMaterial;

class ProductMaterial extends Model
{
    use HasFactory;

    protected $table = 'product_material';
    protected $guarded = [''];


    public function warehouseMaterials()
    {
        return $this->hasMany(WareHouseMaterial::class,'material_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class,'material_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
