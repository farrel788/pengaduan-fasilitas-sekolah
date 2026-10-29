<?php

namespace App\Http\Controllers;

use App\Http\Resources\AreaResource;
use App\Models\Area;

class AreaController extends Controller
{
    public function index()
    {
        $areas = Area::with('locations')->orderBy('name')->get();

        return AreaResource::collection($areas);
    }
}
