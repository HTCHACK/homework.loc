<?php

namespace App\Http\Controllers\WareHouse;

use App\Http\Controllers\Controller;
use App\Models\WareHouseMaterial\WareHouseMaterial;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SaleRequest;
use Illuminate\Http\Request;
use App\Models\Sales\Sale;

class WareHouseMaterialsController extends Controller
{
    public function __construct()
    {   

        // $this->middleware('auth:api');
        // $this->middleware('permissions:warehousematerials_access')->only('index');
        // $this->middleware('permissions:warehousematerials_create')->only('store');
        // $this->middleware('permissions:warehousematerials_show')->only('show');
        // $this->middleware('permissions:warehousematerials_update')->only('update');
        // $this->middleware('permissions:warehousematerials_delete')->only('destroy');

    }

    public function index()
    {
        $warehousematerial = WareHouseMaterial::query()
        ->select('warehouse_materials.reminder','warehouse_materials.material_id',
        DB::raw('SUM(warehouse_materials.reminder) as available'),
        DB::raw('SUM(warehouse_materials.reminder * warehouse_materials.buy_price) as mediumCost'),
        DB::raw('materials.name as material'))
        ->leftJoin('materials', function ($join) {
            $join->on('warehouse_materials.material_id', '=', 'materials.id');
        })
        ->groupBy('warehouse_materials.material_id')
        ->get();
        
        return response()->json(['warehousematerial'=>$warehousematerial]);
    }

    public function show($id)
    {
        $show = WareHouseMaterial::query()
        ->select('warehouse_materials.ware_house_id','warehouse_materials.warehouse_materialable_id',
        'warehouse_materials.recieve','warehouse_materials.reminder',
        'warehouse_materials.buy_price','warehouse_materials.created_at',
        DB::raw('ware_houses.name as warehouse'),
        DB::raw('materials.name as material'))
        ->leftJoin('ware_houses', function ($join) {
            $join->on('warehouse_materials.ware_house_id', '=', 'ware_houses.id');
        })
        ->leftJoin('materials', function ($join) {
            $join->on('warehouse_materials.material_id', '=', 'materials.id');
        })
        ->where('warehouse_materials.material_id',$id)
        ->get();

        return response()->json(['show'=>$show]);
    }

    public function report(SaleRequest $request)
    {
        
        
        return response()->json(['costRate'=>'Created']);

    }
}
