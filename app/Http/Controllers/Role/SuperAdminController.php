<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;

class SuperAdminController extends Controller
{   

    public function __construct()
    {   
        $this->middleware('auth:api');
        $this->middleware('role');
    }

    public function index()
    {   
        if(Auth::user()->role_id != Role::IS_SUPER_ADMIN){
            return response(['message'=>'Permission denied']);
        }

        return response(['message'=>'Super Admin']);
    }
}
