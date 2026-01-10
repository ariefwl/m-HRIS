<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;

class EmployeeController extends Controller
{
    public function empById()
    {
        $employee = auth()->user()->employee;

        return ApiResponse::success('Employee data',$employee);
    }
}
