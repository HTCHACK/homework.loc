<?php

namespace App\Http\Controllers\Products;

use App\Models\WareHouseMaterial\WareHouseMaterial;
use App\Models\WareHouseMaterial\WareHouse;
use App\Models\Product\ProductMaterial;
use App\Http\Requests\ProductRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaleRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Material\Material;
use App\Models\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use SebastianBergmann\CodeCoverage\CrapIndex;

use function PHPSTORM_META\type;

class ProductController extends Controller
{
    // public $taken_materials = [];
    public $global_taken_materials = [];
    public $second_materials = [];


    public function __construct()
    {
        //$this->taken_materials = [];
        $this->global_taken_materials = [];
        $this->second_materials = [];

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

        return response()->json(
            [
                'all' => $all
            ]
        );
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


    public function reportOne(SaleRequest $request)
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


    public function report(SaleRequest $request)
    {

        if (is_array($sales = $request['sales'])) {
            $product = $this->ProductCollection($sales);
        }

        //$warehouse_materials = WareHouseMaterial::all();

        // foreach ($warehouse_materials as $warehouse_material) {
        //     WareHouseMaterial::find($warehouse_material->id)->update(['reminder' => $warehouse_material->recieve]);
        // }

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

        $material = collect($product_materials)->map(fn ($product_material) => [
            'material_id' => $product_material->material_id,
            'material_name' => Material::find($product_material->material_id)->name,
            'totalqty' => $product_material->qty,
            'warehouses' => $this->WareHouseCollection($product_material->material_id, $product_material->qty),
        ]);

        return $material;
    }



    public function WareHouseCollection($material_id, $totalQty)
    {

        $collected_materials = [];

        $warehouse_materials = WareHouseMaterial::query()->where('material_id', $material_id)->get();

        $reminder = $totalQty;

        foreach ($warehouse_materials as $warehouse_material) {

            $isExist = collect($this->global_taken_materials)->contains("warehouse_materials_id", $warehouse_material->id); //true false

            //agar bor bolsa
            if ($isExist) {

                foreach ($this->global_taken_materials as $key => $material) {

                    if ($material['lack'] > 0 && $material['warehouse_materials_id'] == $warehouse_material->id && $material['material_id'] == $material_id) {

                        $subtraction = $material['lack'] - $reminder; // 5 - 10 < 0 ? 5 - 2 > 0

                        if ($subtraction >= 0) {
                            //  
                            array_push($collected_materials, [
                                'warehouse_materials_id' => $material['warehouse_materials_id'],
                                'warehouse_id' => $material['warehouse_id'],
                                'buy_price' => $material['buy_price'],
                                'warehouse' => $material['warehouse'],
                                'material_id' => $material['material_id'],
                                'material' => $material['material'],
                                'quantity' => $reminder,
                                'lack' => $subtraction
                            ]);

                            if ($material['material_id']) {
                                $this->global_taken_materials[$key]['lack'] = $subtraction;
                            }

                            // $index = array_search($material['warehouse_materials_id'], array_column($this->global_taken_materials, 'warehouse_materials_id'));
                            // $this->global_taken_materials[$index]['lack'] = $multipul;

                            break;
                        } elseif ($subtraction < 0) {

                            $reminder -= $material['lack'];

                            array_push($collected_materials, [
                                'warehouse_materials_id' => $material['warehouse_materials_id'],
                                'warehouse_id' => $material['warehouse_id'],
                                'buy_price' => $material['buy_price'],
                                'warehouse' => $material['warehouse'],
                                'material_id' => $material['material_id'],
                                'material' => $material['material'],
                                'quantity' => $material['lack'],
                                'lack' => 0
                            ]);

                            if ($material['material_id']) {
                                $this->global_taken_materials[$key]['lack'] = 0;
                            }

                            // $index = array_search($material['warehouse_materials_id'], array_column($this->global_taken_materials, 'warehouse_materials_id'));
                            // $this->global_taken_materials[$index]['lack'] = 0;

                            continue;
                        }
                    }
                }
            } else {
                if ($warehouse_material->reminder >= $reminder) {

                    array_push($collected_materials, [
                        'warehouse_materials_id' => $warehouse_material->id,
                        'warehouse_id' => $warehouse_material->ware_house_id,
                        'buy_price' => $warehouse_material->buy_price,
                        'warehouse' => WareHouse::find($warehouse_material->ware_house_id)->name,
                        'material_id' => $material_id,
                        'material' => Material::find($warehouse_material->material_id)->name,
                        'quantity' => $reminder,
                        'lack' => $warehouse_material->reminder - $reminder
                    ]);

                    array_push($this->global_taken_materials, [
                        'warehouse_materials_id' => $warehouse_material->id,
                        'warehouse_id' => $warehouse_material->ware_house_id,
                        'buy_price' => $warehouse_material->buy_price,
                        'warehouse' => WareHouse::find($warehouse_material->ware_house_id)->name,
                        'material_id' => $material_id,
                        'material' => Material::find($warehouse_material->material_id)->name,
                        'quantity' => $reminder,
                        'lack' => $warehouse_material->reminder - $reminder
                    ]);

                    break;
                } else {

                    $reminder -= $warehouse_material->reminder;


                    array_push($collected_materials, [
                        'warehouse_materials_id' => $warehouse_material->id,
                        'warehouse_id' => $warehouse_material->ware_house_id,
                        'buy_price' => $warehouse_material->buy_price,
                        'warehouse' => WareHouse::find($warehouse_material->ware_house_id)->name,
                        'material_id' => $material_id,
                        'material' => Material::find($warehouse_material->material_id)->name,
                        'quantity' => $warehouse_material->reminder,
                        'lack' => 0

                    ]);


                    array_push($this->global_taken_materials, [
                        'warehouse_materials_id' => $warehouse_material->id,
                        'warehouse_id' => $warehouse_material->ware_house_id,
                        'buy_price' => $warehouse_material->buy_price,
                        'warehouse' => WareHouse::find($warehouse_material->ware_house_id)->name,
                        'material_id' => $material_id,
                        'material' => Material::find($warehouse_material->material_id)->name,
                        'quantity' => $warehouse_material->reminder,
                        'lack' => 0
                    ]);

                    continue;
                }
            }
        }

        $tot = array_sum(array_map(function ($taken_material) {
            return $taken_material['quantity'];
        }, $collected_materials));

        $cost = array_sum(array_map(function ($taken_material) {
            return $taken_material['quantity'] * $taken_material['buy_price'];
        }, $collected_materials));

        $not_enough = $totalQty - $tot;

        return [
            'warehouse' => $collected_materials,
            'need' => $not_enough,
            'cost' => $cost,
            'exist' => $isExist
        ];
    }
}
