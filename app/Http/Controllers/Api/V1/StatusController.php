<?php

namespace App\Http\Controllers\Api\V1;


use App\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StatusController extends Controller
{
    public function index() {
        $status = Status::all();

        return response()->json([
            'success' => true,
            'data' => $status
        ]);
    }
}
