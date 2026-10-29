<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportResource;
use App\Models\Report;
use Illuminate\Http\Request;

class TechnicianTaskController extends Controller
{
    private array $with = ['user', 'location.area', 'facilityCategory', 'photos'];

    public function index(Request $request)
    {
        $tasks = Report::with($this->with)
            ->where('assigned_to', $request->user()->id)
            ->latest()
            ->get();

        return ReportResource::collection($tasks);
    }

    public function show(Request $request, Report $report)
    {
        $this->pastikanMilik($request, $report);

        return new ReportResource($report->load($this->with));
    }

    public function updateStatus(Request $request, Report $report)
    {
        $this->pastikanMilik($request, $report);
        abort_if($report->status === 'selesai', 422, 'Tugas ini sudah selesai.');

        $data = $request->validate([
            'status'   => ['required', 'in:diproses,selesai'],
            'note'     => ['required_if:status,selesai', 'nullable', 'string', 'max:1000'],
            'photos'   => ['nullable', 'array', 'max:2'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        foreach ($request->file('photos', []) as $file) {
            $report->photos()->create([
                'path' => $file->store('report-results', 'public'),
                'type' => 'hasil',
            ]);
        }

        $report->update([
            'status'          => $data['status'],
            'technician_note' => $data['note'] ?? $report->technician_note,
            'completed_at'    => $data['status'] === 'selesai' ? now() : null,
        ]);

        return new ReportResource($report->fresh($this->with));
    }

    private function pastikanMilik(Request $request, Report $report): void
    {
        abort_unless($report->assigned_to === $request->user()->id, 404);
    }
}