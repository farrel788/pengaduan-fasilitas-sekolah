<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\FacilityCategory;
use App\Models\Location;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    public function index(string $type)
    {
        $rows = match ($type) {
            'kategori-area' => Area::withCount('locations')->orderBy('name')->get()
                ->map(fn ($a) => ['id' => $a->id, 'nama' => $a->name, 'jumlah' => $a->locations_count]),
            'lokasi-spesifik' => Location::with('area')->orderBy('name')->get()
                ->map(fn ($l) => ['id' => $l->id, 'nama' => $l->name, 'kategori' => $l->area?->name]),
            'kategori-fasilitas' => FacilityCategory::orderBy('name')->get()
                ->map(fn ($f) => ['id' => $f->id, 'nama' => $f->name]),
            default => abort(404),
        };

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request, string $type)
    {
        $data = $this->validated($request, $type);

        match ($type) {
            'kategori-area'      => Area::create(['name' => $data['name']]),
            'lokasi-spesifik'    => Location::create(['name' => $data['name'], 'area_id' => $data['area_id']]),
            'kategori-fasilitas' => FacilityCategory::create(['name' => $data['name']]),
            default              => abort(404),
        };

        return response()->json(['message' => 'Data ditambahkan.'], 201);
    }

    public function update(Request $request, string $type, int $id)
    {
        $data = $this->validated($request, $type);
        $row = $this->find($type, $id);

        $row->name = $data['name'];
        if ($type === 'lokasi-spesifik') {
            $row->area_id = $data['area_id'];
        }
        $row->save();

        return response()->json(['message' => 'Data diperbarui.']);
    }

    public function destroy(string $type, int $id)
    {
        $row = $this->find($type, $id);

        abort_if(
            $type === 'kategori-area' && $row->locations()->exists(),
            422, 'Kategori masih memiliki lokasi. Hapus lokasinya terlebih dahulu.'
        );

        $row->delete();

        return response()->json(['message' => 'Data dihapus.']);
    }

    private function find(string $type, int $id)
    {
        return match ($type) {
            'kategori-area'      => Area::findOrFail($id),
            'lokasi-spesifik'    => Location::findOrFail($id),
            'kategori-fasilitas' => FacilityCategory::findOrFail($id),
            default              => abort(404),
        };
    }

    private function validated(Request $request, string $type): array
    {
        $rules = ['name' => ['required', 'string', 'max:100']];
        if ($type === 'lokasi-spesifik') {
            $rules['category'] = ['required', 'exists:areas,name'];
        }

        $data = $request->validate($rules);
        if ($type === 'lokasi-spesifik') {
            $data['area_id'] = Area::where('name', $data['category'])->value('id');
        }

        return $data;
    }
}