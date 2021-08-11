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

    //     Asadbek Xalimjonv, [10.08.21 22:51]
    // product_material : material_id, quantity, product_id , 
    // warehouse dan material_id buy_price va warehouse_id ,

    // Asadbek Xalimjonv, [10.08.21 22:55]
    // material_id boyicha reminder ni hisoblayman

    public function index()
    {

        $all = Product::all();

        return response()->json(['all' => $all]);
    }

    public function costRate()
    {
        // SELECT products.name, products.id, product_material.material_id , 
        // product_material.quantity , warehouse_materials.buy_price , warehouse_materials.ware_house_id 
        // FROM products JOIN product_material ON products.id = product_material.product_id 
        // JOIN materials ON product_material.material_id = materials.id 
        // JOIN warehouse_materials on product_material.material_id = warehouse_materials.material_id
        // WHERE products.id = 2;



        $product_id = 3;
        $quantity = 4;

        $calculate = ProductMaterial::query()
            ->select(
                'product_material.*',
                DB::raw('warehouse_materials.buy_price as price'),
                DB::raw('product_material.quantity as quantity'),
                DB::raw('SUM(warehouse_materials.reminder)'),
                DB::raw('materials.name as material'),
                DB::raw('products.name as product'),
                DB::raw('ware_houses.name as warehouse'),
                DB::raw('SUM(warehouse_materials.reminder)')
            )
            ->join('materials', 'product_material.material_id', '=', 'materials.id')
            ->join('warehouse_materials', 'product_material.material_id', '=', 'warehouse_materials.material_id')
            ->join('products', 'product_material.product_id', '=', 'products.id')
            ->join('ware_houses', 'warehouse_materials.ware_house_id', '=', 'ware_houses.id')
            ->groupBy('product_material.product_id')
            ->groupBy('warehouse_materials.material_id')
            ->orderBy('warehouse_materials.material_id', 'asc')
            ->where('product_material.product_id', $product_id)
            ->get();

        return response()->json([
            'calculate' => $calculate,
            'success' => true
        ]);
    }

    public function report(Request $request)
    {
        foreach($request->products as $key=>$value)
        {

        }
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
