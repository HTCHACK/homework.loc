<?php

namespace App\Http\Controllers\BuyMaterial;

use Illuminate\Support\Facades\DB;  
use App\Http\Requests\BuyMaterialRequest;
use App\Models\BuyMaterial\BuyMaterial;
use App\Http\Controllers\Controller;
use App\Models\WareHouseMaterial\WareHouseMaterial;
use App\Models\BuyMaterial\BuyingMaterialItem;
use App\Http\Requests\BuyMaterialItemRequest;
use App\Models\CounterAgency;


class BuyMaterialController extends Controller
{
    public function __construct()
    {   

        // $this->middleware('auth:api');
        // $this->middleware('permissions:buy_material_access')->only('index');
        // $this->middleware('permissions:buy_material_create')->only('store');
        // $this->middleware('permissions:buy_material_show')->only('show');
        // $this->middleware('permissions:buy_material_update')->only('update');
        // $this->middleware('permissions:buy_material_delete')->only('destroy');
        // $this->middleware('permissions:buy_material_getitem')->only('getItem');
        // $this->middleware('permissions:buy_material_getItemhistory')->only('getItemhistory');
        // $this->middleware('permissions:buy_material_send')->only('send');
    }


    public function index()
    {
        //First Way
        $buyMaterials = BuyMaterial::query()
        ->select('buy_material.*',DB::raw('sum(buying_material_item.quantity * buying_material_item.price) as total'),
        DB::raw('counter_agencies.name as agency'))
        ->leftJoin('buying_material_item', function ($join) {
            $join->on('buy_material.id','=','buying_material_item.buy_material_id');
        })
        ->leftJoin('counter_agencies', function ($join) {
            $join->on('buy_material.counter_agency_id','=','counter_agencies.id');
        })
        ->groupBy('buy_material.id') 
        ->get();

        //Second Way

        // $buyMaterials = BuyMaterial::withCount(['materials AS total' => function ($query) {
        //     $query->select(DB::raw('SUM(buying_material_item.quantity * buying_material_item.price) as total'));
        // }])->get();

        //Third Way
        // $buyMaterials = BuyMaterial::addSelect(['total' => BuyingMaterialItem::select(DB::raw('SUM(buying_material_item.quantity * buying_material_item.price)'))
        // ->whereColumn('buying_material_item.buy_material_id', 'buy_material.id')
        //  ])->get();

        return response()->json(['all'=>$buyMaterials]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BuyMaterialRequest $request)
    {
        $buyMaterial = BuyMaterial::create($request->all());    
        
        $buyMaterialItems = collect($request->items)->map(fn($buyMaterialItem) => [
            "material_id" => $buyMaterialItem['material_id'],
            "price"=>$buyMaterialItem['price'],
            "quantity" => $buyMaterialItem['quantity']
        ]);
        

        $buyMaterial->materials()->attach($buyMaterialItems);

        $total = BuyingMaterialItem::select(DB::raw('sum(quantity * price) as total'))->whereIn('buy_material_id',$buyMaterial)->get();

        return response()->json(['created'=>'Successfully Created','total'=>$total]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {   

        $show = BuyMaterial::addSelect(['total' => BuyingMaterialItem::select(DB::raw('SUM(buying_material_item.quantity * buying_material_item.price)'))
        ->whereColumn('buying_material_item.buy_material_id', 'buy_material.id'),
        'agency' => CounterAgency::select(DB::raw('counter_agencies.name'))
        ->whereColumn('counter_agencies.id', 'buy_material.counter_agency_id')
        ])
        ->find($id);  

        //$show = $show = BuyMaterial::with('materials')->find($id);

        return response()->json(['one'=>$show]);
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(BuyMaterialRequest $request, BuyMaterial $buyMaterial)
    {   

        $data = $request->validated();
       
        $buyMaterial->update($data);    

        $buyMaterial->materials()->sync($this->buyMaterialItems($data['items']));

        $total = BuyingMaterialItem::select(DB::raw('sum(quantity * price) as total'))->whereIn('buy_material_id',$buyMaterial)->get();

        return response()->json(['updatd'=>'Successfully Updated']);

    }

    private function buyMaterialItems($items)
    {
        return collect($items)->mapWithKeys(function ($buyMaterialItem) {
            return [
                $buyMaterialItem['material_id'] => [
                    "price"=>$buyMaterialItem['price'],
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
        BuyMaterial::destroy($id);

        return response()->json(['deleted'=>'Buy Material Successfully Deleted']);
    }


    public function getItem($id)
    {   
        $getItem = BuyingMaterialItem::query()
        ->select('buying_material_item.quantity','buying_material_item.material_id','buying_material_item.id','buying_material_item.price',     
        DB::raw('buying_material_item.quantity - SUM(warehouse_materials.recieve) as lack'),
        DB::raw('materials.name as material'))
        ->leftJoin('warehouse_materials', function ($join) {
            $join->on('buying_material_item.id','=','warehouse_materials.warehouse_materialable_id');
            $join->where('warehouse_materials.warehouse_materialable_type','buying_material_item');
        })      
        ->leftJoin('materials', function ($join) {
            $join->on('buying_material_item.material_id','=','materials.id');
        })
        ->groupBy('warehouse_materials.warehouse_materialable_id')
        ->groupBy('buying_material_item.id') 
        ->where('buying_material_item.buy_material_id',$id)
        ->get();

        return response()->json(['getItem'=>$getItem]);    
    }

    public function getItemhistory($id) 
    {

        $getItemhistory = WareHouseMaterial::query()
        ->select('warehouse_materials.*',
        DB::raw('DATEDIFF(current_date, warehouse_materials.created_at)  as days_in_warehouse'),
        DB::raw('ware_houses.name as warehouse'),
        DB::raw('materials.name as material'))
        ->leftJoin('buying_material_item', function ($join) {
            $join->on('warehouse_materials.warehouse_materialable_id','=','buying_material_item.id');
        })
        ->leftJoin('ware_houses', function ($join) {
            $join->on('warehouse_materials.ware_house_id','=','ware_houses.id');
        })
        ->leftJoin('materials', function ($join) {
            $join->on('warehouse_materials.material_id','=','materials.id');
        })
        ->where('buying_material_item.buy_material_id',$id)
        ->with('warehouse_materialable')
        ->get();

        return response()->json(['getItemhistory'=>$getItemhistory]);
    }   
    

    public function send(BuyMaterialItemRequest $request)
    {       
        // $id = $request->buy_material_id;
        // $buyMaterial = BuyMaterial::find($id);
        // $buyMaterialItems = BuyingMaterialItem::where('buy_material_id',$buyMaterial)->get();
        
        foreach($request->warehouse_materials as $key=>$value)
        {                  
                                                  
            WareHouseMaterial::create([
                'material_id'=>$value['material_id'],
                'ware_house_id'=>$value['ware_house_id'],
                'recieve'=>$value['recieve'],
                'reminder'=>$value['recieve'],
                'buy_price'=>$value['price'],
                'warehouse_materialable_id'=>$value['warehouse_materialable_id'],
                'warehouse_materialable_type'=>'buying_material_item'
            ]);                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  
        }   

        return response()->json(['success'=>'Successfully Created']);

    }

                        
}
