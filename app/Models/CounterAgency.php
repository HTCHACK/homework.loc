<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BuyMaterial\BuyMaterial;
use DateTimeInterface;

class CounterAgency extends Model
{
    use HasFactory;

    protected $table = 'counter_agencies';
    protected $guarded = [''];

    public function buyMaterials()
    {
        return $this->belongsToMany(BuyMaterial::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
