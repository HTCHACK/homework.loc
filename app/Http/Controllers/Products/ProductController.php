<?php

namespace App\Http\Controllers\Products;

use App\Models\WareHouseMaterial\WareHouseMaterial;
use App\Models\Product\ProductMaterial;
use App\Http\Requests\ProductRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SaleRequest;
use App\Models\Material\Material;
use App\Models\Product\Product;
use App\Models\WareHouseMaterial\WareHouse;
use Illuminate\Http\Request;


class ProductController extends Controller
{
    public $taken_materials = [];

    public function __construct()
    {
        $this->taken_materials = [];
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


    public function reportNew(SaleRequest $request)
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


//Second


    public function report(Request $request)
    {

        if (is_array($sales = $request['sales'])) {
            $product = $this->ProductCollection($sales);
        }

        return response()->json([
            "result" => [
                "data" => [
                    "report_product_materials" => [
                        'product' => $product
                    ]
                ],
            ],
            "success" => true
        ]);
    }

    public function ProductCollection($sales)
    {

        $products = collect($sales)->map(fn ($product) => [
            "product_name" => Product::find($product['product_id'])->name,
            "product_id" => $product['product_id'],
            "quantity" => $product['quantity'],
            "product_materials" => $this->MaterialCollection($product['quantity'], $product['product_id'])
        ]);

        return $products;
    }

    public function MaterialCollection($quantity, $product_id)
    {

        $product_materials = ProductMaterial::query()->select('product_id', 'material_id', 'quantity', DB::raw("quantity*'$quantity' as qty"))->where('product_id', $product_id)->get();

        $material = $product_materials->map(function ($product_material) {
            return [
                "material_id" => $product_material->material_id,
                "quantity" => $product_material->quantity,
                "qty" => $product_material->qty,
                "take" => $this->WareHouseCollection($product_material->material_id, $product_material->qty)
            ];
        });

        return $material;
    }


    public function WareHouseCollection($material_id, $totalQty)
    {
        $taken_finished = [];

        $warehouse_materials = WareHouseMaterial::query()
            ->select(
                'id',
                'material_id',
                'reminder',
                'ware_house_id',
                'buy_price'
            )
            ->where('material_id', $material_id)->get();

        foreach ($warehouse_materials as $warehouse_material) 
        {
            // TODO:: find ($this->taken_materials, 'id, $warehouse_material->id)
            $find_item = WareHouseMaterial::find($this->taken_materials, 'id', $warehouse_material->id);

            //agar bor bolsa
            if ($find_item) 
            {

                $warehouse_material->id;
                $warehouse_material->remainder - $find_item->need;
                $warehouse_material->material_id;
                $warehouse_material->ware_house_id;
                $warehouse_material->buy_price;

                //yetarli bolsa

                if ($find_item->take == $totalQty) 
                {
                    array_push($taken_finished, $this->taken_materials);
                    break;
                } 
                else 
                {

                    array_push($this->taken_materials, [
                        'id' => $warehouse_material->id,
                        'material_id' => $material_id,
                        'ware_house_id' =>  $warehouse_material->ware_house_id,
                        'buy_price' => $warehouse_material->buy_price,
                        'need' => ($warehouse_material->remainder - $find_item->need),
                    ]);

                    continue;

                }
            }
             else 
            {

                $id = $warehouse_material->id;
                $material_id = $warehouse_material->material_id;
                $ware_house_id = $warehouse_material->ware_house_id;
                $buy_price = $warehouse_material->buy_price;
                $need = $totalQty - $warehouse_material->remainder;

                //10 - 2
                //5 - 6

                if ($need > 0) {

                    array_push(
                        $this->taken_materials, 
                        [
                        'id' => $id,
                        'material_id' => $material_id,
                        'ware_house_id' => $ware_house_id,
                        'buy_price' => $buy_price,
                        'lack' => 0,
                        'need' => $need,
                        ]
                    );
                } 
                elseif ($need < 0) 
                {
                    array_push($this->taken_materials, 
                        [
                            'id' => $id,
                            'material_id' => $material_id,
                            'ware_house_id' => $ware_house_id,
                            'buy_price' => $buy_price,
                            'lack' => abs($need),
                            'take' => $totalQty,
                        ]
                    );
                }
            }
        }

        return $taken_finished;
    }

}
