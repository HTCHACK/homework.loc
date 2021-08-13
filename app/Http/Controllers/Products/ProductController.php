<?php

namespace App\Http\Controllers\Products;

use App\Http\Requests\ProductRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Product\Product;
use App\Models\Product\ProductMaterial;
use App\Models\Sales\Sale;
use Illuminate\Http\Request;
use App\Http\Requests\SaleRequest;
use App\Models\WareHouseMaterial\WareHouseMaterial;

class ProductController extends Controller
{

    public function __construct()
    {

        // $this->middleware('auth:api');
        // $this->middleware('permissions:product_access')->only('index');
        // $this->middleware('permissions:product_create')->only('store');
        // $this->middleware('permissions:product_show')->only('show');
        // $this->middleware('permissions:product_update')->only('update');
        // $this->middleware('permissions:product_delete')->only('destroy');

    }

    public function index()
    {

        $all = Product::all();

        return response()->json(['all' => $all]);
    }

    public function productMaterials()
    {

        $all = Product::with('productMaterials')->get();

        return response()->json(['all' => $all]);
    }

    public function report(SaleRequest $request)
    {
        // $values = collect($request->sales)->map(fn ($value) => [
        //     'product_id' => $value['product_id'],
        //     'quantity' => $value['quantity'],
        //     'product_name' => Product::query()->select('products.name')->where('id',$value['product_id'])->get(),
        //     $quantity = $value['quantity'],

        //     $qty = ProductMaterial::query()->select('product_material.*',DB::raw("product_material.quantity*'$quantity' as totalqty"),)
        //     ->where('product_material.product_id', $value['product_id'])
        //     ->get(),


        //     'calculate' => ProductMaterial::query()
        //         ->select(
        //             'product_material.*',
        //             DB::raw('warehouse_materials.buy_price as price'),
        //             DB::raw("product_material.quantity as quantity"),
        //             DB::raw('SUM(warehouse_materials.reminder) as totalreminder'),
        //             DB::raw('materials.name as material'),
        //             DB::raw("ware_houses.name as warehouse"),
        //             DB::raw("product_material.quantity*'$quantity' as totalqty"),
        //             DB::raw("product_material.quantity*'$quantity' - SUM(warehouse_materials.reminder)"),
        //         )
        //         ->join('materials', 'product_material.material_id', '=', 'materials.id')
        //         ->join('warehouse_materials', 'product_material.material_id', '=', 'warehouse_materials.material_id')
        //         ->join('products', 'product_material.product_id', '=', 'products.id')
        //         ->join('ware_houses', 'warehouse_materials.ware_house_id', '=', 'ware_houses.id')
        //         ->groupBy('product_material.product_id')
        //         ->groupBy('warehouse_materials.material_id')
        //         ->orderBy('warehouse_materials.material_id', 'asc')
        //         ->orderBy('warehouse_materials.id', 'asc')
        //         ->where('product_material.product_id', $value['product_id'])
        //         ->get(),
        // ]);


        // return response()->json([
        //     'result' => [
        //         'data' => [
        //             'report_product_materials' => [
        //                 'product' => $values,
        //             ],
        //         ],

        //         'success' => true
        //     ],
        // ]);

        $reportData = [];

        foreach ($request->sales as $key => $value) {
            $product_id = $value['product_id'];
            $quantity = $value['quantity'];
            $warehouse = 'warehouse';

            $qty = ProductMaterial::query()->select('product_material.quantity',DB::raw("product_material.quantity*'$quantity' as totalqty"),)
            ->where('product_material.product_id', $value['product_id'])
            ->get();

            $warehouse_materials = WareHouseMaterial::query()->select('warehouse_materials.reminder',
            'warehouse_materials.buy_price','warehouse_materials.material_id','warehouse_materials.ware_house_id'
            )
            ->join('product_material','warehouse_materials.material_id','=','product_material.material_id')
            ->where('product_material.product_id',$value['product_id'])
            ->get();


            foreach($warehouse_materials as $key=>$wareId)
            {

                $reminderId = $wareId->reminder;
                $calculate = ProductMaterial::query()
                ->select(
                    'product_material.*',
                    DB::raw('warehouse_materials.buy_price as price'),
                    DB::raw('materials.name as material'),
                    DB::raw("ware_houses.name as warehouse"),
                    DB::raw("(product_material.quantity*'$quantity') - '$reminderId'"),
                )
                ->join('materials', 'product_material.material_id', '=', 'materials.id')
                ->join('warehouse_materials', 'product_material.material_id', '=', 'warehouse_materials.material_id')
                ->join('products', 'product_material.product_id', '=', 'products.id')
                ->join('ware_houses', 'warehouse_materials.ware_house_id', '=', 'ware_houses.id')
                ->groupBy('warehouse_materials.material_id')
                ->orderBy('warehouse_materials.material_id', 'asc')
                ->orderBy('warehouse_materials.id', 'asc')
                ->get();
            }
                    

            array_push($reportData, [
                'product' => [
                    'value' => $value,
                    'calculate'=>$calculate,
                    'warehouse_materials'=>$warehouse_materials
                ]
            ]);
        }

        return response()->json([
            'result' => [
                'data' => [
                    'report_product_materials' => $reportData,
                ],
                'success' => true
            ],
        ]); 
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {

        $product = Product::create($request->all());

        $materials = collect($request->product_material)->map(fn ($material) => [
            "material_id" => $material['material_id'],
            "quantity" => $material['quantity']
        ]);

        $product->materials()->attach($materials);

        return response()->json(['created' => 'Successfully Created']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        return Product::query()->select('products.id', 'products.name')->findorFail($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, Product $product)
    {


        $data = $request->validated();
        $product->update($data);
        $product->materials()->sync($this->mapMaterials($data['product_material']));

        return response()->json(['updated' => 'Successfully Updated']);
    }

    private function mapMaterials($items)
    {
        return collect($items)->mapWithKeys(function ($buyMaterialItem) {
            return [
                $buyMaterialItem['material_id'] => [
                    "quantity" => $buyMaterialItem['quantity']
                ]
            ];
        });
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id 
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        Product::destroy($id);

        return response()->json(['deleted' => 'Successfully Deleted']);
    }
}
