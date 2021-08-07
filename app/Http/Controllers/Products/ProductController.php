<?php

namespace App\Http\Controllers\Products;

use App\Http\Requests\ProductRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Product\Product;
use Illuminate\Http\Request;


class ProductController extends Controller
{

    public function __construct()
    {   

        $this->middleware('auth:api');
        $this->middleware('permissions:product_access')->only('index');
        $this->middleware('permissions:product_create')->only('store');
        $this->middleware('permissions:product_show')->only('show');
        $this->middleware('permissions:product_update')->only('update');
        $this->middleware('permissions:product_delete')->only('destroy');

    }

    public function index()
    {
        
        return Product::all();

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
       
        $materials = collect($request->product_material)->map(fn($material) => [
            "material_id" => $material['material_id'],
            "quantity" => $material['quantity']
        ]);

        $product->materials()->attach($materials);
    
        return response()->json(['created'=>'Successfully Created']);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        return Product::findorFail($id);

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
      
        return response()->json(['updated'=>'Successfully Updated']);

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

        return response()->json(['deleted'=>'Successfully Deleted']);

    }
}
