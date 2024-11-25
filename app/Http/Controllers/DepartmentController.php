<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        // Fetch all departments
        $departments = Department::select('id', 'name')->get();

        // Return as JSON response
        return response()->json([
            'message' => 'Departments retrieved successfully',
            'data' => $departments,
        ]);
    }
}
