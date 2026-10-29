<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([]);
    }

    public function readAll(Request $request)
    {
        return response()->noContent();
    }
}
