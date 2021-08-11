<?php

namespace App\Http\Controllers\Materials;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaterialRequest;
use App\Models\Material\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function __construct()
    {   

        // $this->middleware('auth:api');
        // $this->middleware('permissions:material_access')->only('index');
        // $this->middleware('permissions:material_create')->only('store');
        // $this->middleware('permissions:material_show')->only('show');
        // $this->middleware('permissions:material_update')->only('update');
        // $this->middleware('permissions:material_delete')->only('destroy');

    }

    public function index()
    {
        return Material::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MaterialRequest $request)
    {
        Material::create($request->all());

        return response()->json(['created'=>'Material Successfully Created']);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Material::findorFail($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(MaterialRequest $request, Material $material)
    {
        $data = $request->validated();
        $material->update($data);

        return response()->json(['updated'=>'Material Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Material::destroy($id);

        return response()->json(['deleted'=>'Material Successfully Deleted']);
    }
}
