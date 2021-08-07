<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;

class AdminController extends Controller
{   

    public function __construct()
    {   
        $this->middleware('auth:api');
        $this->middleware('role');
    }

    public function index()
    {   
        if(Auth::user()->role_id != Role::IS_ADMIN){
            return response(['message'=>'Permission denied']);
        }
        return response(['message'=>'Admin']);
    }

}
