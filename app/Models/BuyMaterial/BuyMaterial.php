<?php

namespace App\Models\BuyMaterial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BuyMaterial\BuyingMaterialItem;
use Illuminate\Database\Eloquent\Model;
use App\Models\CounterAgency;
use App\Models\Material\Material;
use DateTimeInterface;
use App\Models\WareHouseMaterial\WareHouseMaterial;
use App\Models\WareHouseMaterial\WareHouse;

class BuyMaterial extends Model
{
    use HasFactory;

    protected $table = 'buy_material';
    protected $guarded = [''];

    public const TABLE_NAME = 'buying_material_item';

    public function agencies()
    {
        return $this->belongsTo(CounterAgency::class);
    }

    public function buyMaterialItem()
    {
        return $this->hasMany(BuyingMaterialItem::class);
    }

    public function warehouseMaterials()
    {
        return $this->morphMany(WareHouseMaterial::class, 'warehouse_materialable');
    }

    
    public function materials()
    {
        return $this->belongsToMany(Material::class,'buying_material_item','buy_material_id','material_id')->withPivot('quantity','price')->withTimestamps();
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d');
    }

    

    
}
