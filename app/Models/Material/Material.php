<?php

namespace App\Models\Material;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BuyMaterial\BuyingMaterialItem;
use App\Models\BuyMaterial\BuyMaterial;
use App\Models\Product\Product;
use DateTimeInterface;

class Material extends Model
{
    use HasFactory;

    protected $table = 'materials';
    protected $guarded = [''];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function buyingMaterialItem()
    {
        return $this->belongsTo(BuyingMaterialItem::class);
    }

    public function buymaterial()
    {
        return $this->belongsToMany(BuyMaterial::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
