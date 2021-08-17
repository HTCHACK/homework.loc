<?php

namespace App\Http\Controllers\Products;

use App\Models\WareHouseMaterial\WareHouseMaterial;
use App\Models\Product\ProductMaterial;
use App\Http\Requests\ProductRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SaleRequest;
use App\Models\Product\Product;
use Illuminate\Http\Request;


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


    public function report(SaleRequest $request)
    {
        $values = collect($request->sales)->map(fn ($value) => [
            'product_id' => $value['product_id'],
            'quantity' => $value['quantity'],
            'product' => Product::query()->select('products.name', 'products.id')->where('id', $value['product_id'])->get(),
            $quantity = $value['quantity'],

            'product_materials' => ProductMaterial::query()->select('product_material.*', DB::raw("product_material.quantity*'$quantity' as totalqty"),)
                ->where('product_material.product_id', $value['product_id'])
                ->get(),



            'calculate' => ProductMaterial::query()
                ->select(
                    'product_material.*',
                    DB::raw('warehouse_materials.buy_price as price'),
                    DB::raw("product_material.quantity as quantity"),
                    DB::raw('SUM(warehouse_materials.reminder) as totalreminder'),
                    DB::raw('materials.name as material'),
                    DB::raw('ware_houses.name as warehouse'),
                    DB::raw("product_material.quantity*'$quantity' as totalqty"),
                    DB::raw("warehouse_materials.reminder - product_material.quantity*'$quantity'"),
                )
                ->join('materials', 'product_material.material_id', '=', 'materials.id')
                ->join('warehouse_materials', 'product_material.material_id', '=', 'warehouse_materials.material_id')
                ->join('products', 'product_material.product_id', '=', 'products.id')
                ->join('ware_houses', 'warehouse_materials.ware_house_id', '=', 'ware_houses.id')
                ->groupBy('product_material.product_id')
                ->groupBy('warehouse_materials.material_id')
                ->orderBy('warehouse_materials.material_id', 'asc')
                ->orderBy('warehouse_materials.id', 'asc')
                ->where('product_material.product_id', $value['product_id'])
                ->get(),
        ]);


        return response()->json([
            'result' => [
                'data' => [
                    'report_product_materials' => [
                        'product' => $values,
                    ],
                ],

                'success' => true
            ],
        ]);
    }

    public function status($quantity, $material_id)
    {
        $collect_data = [];

        $warehouse_materials = WareHouseMaterial::query()->select(
            'warehouse_materials.id',
            'warehouse_materials.material_id',
            'warehouse_materials.reminder',
            'warehouse_materials.ware_house_id',
            'warehouse_materials.buy_price',
            DB::raw('materials.name as material'),
            DB::raw('ware_houses.name as warehouse')
        )
            ->join('materials', 'warehouse_materials.material_id', '=', 'materials.id')
            ->join('ware_houses', 'warehouse_materials.ware_house_id', '=', 'ware_houses.id')
            ->where('warehouse_materials.material_id', $material_id)
            ->orderBy('warehouse_materials.id', 'desc')
            ->get();


        foreach ($warehouse_materials as $warehouse_material) {
            $used_quantity = $quantity - $warehouse_material->reminder;

            if ($used_quantity > 0) {
                $used = [];

                $ware_house_id = $warehouse_material->ware_house_id;
                $reminder = $warehouse_material->reminder;
                $id = $warehouse_material->id;
                $material_id = $warehouse_material->material_id;
                $material_name = $warehouse_material->material;
                $buy_price = $warehouse_material->buy_price;
                $warehouse_name = $warehouse_material->warehouse;
                $need = $used_quantity;



                array_push(
                    $used,
                    [
                        'id' => $id,
                        'warehouse_id' => $ware_house_id,
                        'warehouse' => $warehouse_name,
                        'material_id' => $material_id,
                        'material' => $material_name,
                        'price' => $buy_price,
                        'take' => $reminder,
                        'need' => $need
                    ]
                );
            } elseif ($used_quantity < 0) {
                $used = [];

                $ware_house_id = $warehouse_material->ware_house_id;
                $reminder = $warehouse_material->reminder;
                $id = $warehouse_material->id;
                $material_id = $warehouse_material->material_id;
                $material_name = $warehouse_material->material;
                $buy_price = $warehouse_material->buy_price;
                $warehouse_name = $warehouse_material->warehouse;
                $optional = abs($used_quantity);

                array_push(
                    $used,
                    [
                        'id' => $id,
                        'warehouse_id' => $ware_house_id,
                        'warehouse' => $warehouse_name,
                        'material_id' => $material_id,
                        'material' => $material_name,
                        'price' => $buy_price,
                        'optional' => $optional,
                        'take' => $reminder - $optional,
                    ]
                );
            }
        }

        array_push($collect_data, [
            'materials' => [
                'used_quantity' => $used,
                'quantity' => $quantity,
                'material_id' => $material_id
            ]
        ]);

        return $collect_data;
    }

    public function getProductMaterials($quantity, $product_id)
    {
        $set_reminder = [];

        $product_materials = ProductMaterial::query()
            ->select(
                'product_material.*',
                DB::raw("'$quantity'*product_material.quantity as totalqty")
            )
            ->where('product_material.product_id', $product_id)
            ->with(['material'])
            ->get();

        foreach ($product_materials as $product_material) {

            $status = $this->status(($product_material->quantity * $quantity), $product_material->material_id);

        }

        array_push($set_reminder, ['product_material' => $product_material, 'status' => $status]);

        return $set_reminder;

    }

    public function getProductsWithMaterials($sales)
    {

        $products = [];

        foreach ($sales as $sale) {

            $product = Product::where('id', $sale['product_id'])
                ->select('id', 'name')->get();

            $product_materials = ProductMaterial::where('product_id', $sale['product_id'])->get();

            $product = Product::where('id', $sale['product_id'])->select('id', 'name')->first();

            $materials = collect();

            foreach ($product_materials as $product_material) {

                $materials = $this->getProductMaterials($sale['quantity'], $product_material->material_id, $product_material->quantity, $materials);

            }   

            array_push($products, ['product' => $product, 'qty' => $sale['quantity'], 'product_materials' => $materials, ]);

        }

        return $products;

    }

    public function sale(Request $request)
    {

        $products = [];

        if (is_array($sales = $request['sales'])) {

            $products = $this->getProductsWithMaterials($sales);
        }

        return response()->json([

            'result' => [

                'data' => [

                    'report_product_materials' => $products[0],
                    'total_materials' => '0'

                ],

                'success' => true
            ],
        ]);
    }

}
