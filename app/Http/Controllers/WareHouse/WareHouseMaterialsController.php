<?php

namespace App\Http\Controllers\WareHouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WareHouseMaterialsController extends Controller
{
    public function __construct()
    {   

        $this->middleware('auth:api');
        $this->middleware('permissions:warehousematerials_access')->only('index');
        $this->middleware('permissions:warehousematerials_create')->only('store');
        $this->middleware('permissions:warehousematerials_show')->only('show');
        $this->middleware('permissions:warehousematerials_update')->only('update');
        $this->middleware('permissions:warehousematerials_delete')->only('destroy');

    }

    public function index()
    {
        
    }
}
