<?php

namespace App\Http\Controllers\WareHouse;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Material\Material;

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
        $warehousematerial = Material::query()
            ->select(
                'materials.id',
                'materials.name',
                DB::raw('SUM(warehouse_materials.reminder) as available'),
                DB::raw('SUM(warehouse_materials.reminder * warehouse_materials.buy_price) as mediumCost'),
                DB::raw('materials.name as material')
            )
            ->leftJoin('warehouse_materials', function ($join) {
                $join->on('materials.id', '=', 'warehouse_materials.material_id');
            })
            ->groupBy('warehouse_materials.material_id')
            ->with('warehouseMaterials.warehouses')
            ->get();

        return response()->json(['warehousematerial' => $warehousematerial]);
    }

}
