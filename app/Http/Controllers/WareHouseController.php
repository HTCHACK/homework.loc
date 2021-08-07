<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WareHouseMaterial\WareHouse;

class WareHouseController extends Controller
{
    public function index()
    {
        $all = WareHouse::all();

        return response()->json(['all'=>$all]);
    }
}
