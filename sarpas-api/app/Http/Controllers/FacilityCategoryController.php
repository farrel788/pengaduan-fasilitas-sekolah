<?php

namespace App\Http\Controllers;

use App\Http\Resources\FacilityCategoryResource;
use App\Models\FacilityCategory;

class FacilityCategoryController extends Controller
{
    public function index()
    {
        return FacilityCategoryResource::collection(
            FacilityCategory::orderBy('name')->get()
        );
    }
}
