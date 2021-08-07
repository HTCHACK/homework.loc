<?php

namespace App\Models\WareHouseMaterial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class WareHouse extends Model
{
    use HasFactory;

    protected $table = 'ware_houses';
    protected $guarded = [''];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
