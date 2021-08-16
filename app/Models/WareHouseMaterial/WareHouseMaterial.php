<?php

namespace App\Models\WareHouseMaterial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WareHouseMaterial\WareHouse;
use App\Models\Material\Material;
use App\Models\BuyMaterial\BuyMaterial;

use DateTimeInterface;

class WareHouseMaterial extends Model
{
    use HasFactory;

    protected $table = 'warehouse_materials';
    protected $guarded = [''];

    public function warehouse_materialable()
    {
        return $this->morphTo();
    }

    public function warehouses()
    {
        return $this->belongsTo(WareHouse::class,'ware_house_id');
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function buyMaterial()
    {
        return $this->belongsTo(BuyMaterial::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d-m-Y');
    }

    


}
