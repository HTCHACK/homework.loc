<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PermissionRole;
use App\Models\Permission;
use App\Models\User;
use DateTimeInterface;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $guarded = [''];

    public const IS_SUPER_ADMIN = 1;
    public const IS_ADMIN = 2;
    public const IS_DIRECTOR = 3;

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    
}   