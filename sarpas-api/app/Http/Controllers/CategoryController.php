<?php

namespace App\Http\Controllers;

use App\Http\Resources\AreaResource;
use App\Http\Resources\FacilityCategoryResource;
use App\Models\Area;
use App\Models\FacilityCategory;

class CategoryController extends Controller
{
    public function index()
    {
        $areas = Area::with('locations')->orderBy('name')->get();
        $categories = FacilityCategory::orderBy('name')->get();

        return response()->json([
            'kategori' => FacilityCategoryResource::collection($categories),
            'lokasi' => AreaResource::collection($areas),
            'jenis' => [],
        ]);
    }
}
