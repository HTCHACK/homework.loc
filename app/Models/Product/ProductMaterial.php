<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Material\Material;
use App\Models\Product\Product;
use DateTimeInterface;
use App\Models\WareHouseMaterial\WareHouse;

class ProductMaterial extends Model
{
    use HasFactory;

    protected $table = 'product_material';
    protected $guarded = [''];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
