<?php

namespace App\Models\BuyMaterial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BuyMaterial\BuyMaterial;
use App\Models\Material\Material;
use App\Models\WareHouseMaterial\WareHouse;
use App\Models\WareHouseMaterial\WareHouseMaterial;
use DateTimeInterface;

class BuyingMaterialItem extends Model
{
    use HasFactory;

    protected $table = 'buying_material_item';
    protected $guarded = '';

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function buyMaterial(){
        return $this->belongsTo(BuyMaterial::class);
    }   

    public function warehouseMaterials()
    {
        return $this->morphMany(WareHouseMaterial::class, 'warehouse_materialable');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i');
    }

    protected $casts = [
        'lack' => 'integer',
    ];
    
}
