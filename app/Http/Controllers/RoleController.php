<?php

namespace App\Http\Controllers;

use App\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $role = Role::all();

        return response()->json($role);
    }
}
