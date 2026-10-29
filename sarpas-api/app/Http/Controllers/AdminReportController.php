<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    private array $with = ['user', 'location.area', 'facilityCategory', 'technician', 'photos'];

    public function technicians()
    {
        return User::where('role', 'teknisi')->get();
    }

    // Daftar semua laporan + filter
    public function index(Request $request)
    {
        $reports = Report::with($this->with)
            ->when($request->status,  fn ($q, $v) => $q->where('status', $v))
            ->when($request->urgency, fn ($q, $v) => $q->where('urgency', $v))
            ->when($request->area_id, fn ($q, $v) => $q->whereHas('location', fn ($l) => $l->where('area_id', $v)))
            ->when($request->from,    fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->to,      fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($request->q, function ($q, $v) {
                $q->where(function ($w) use ($v) {
                    $w->where('id', ltrim($v, '#'))
                      ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$v}%"))
                      ->orWhereHas('location', fn ($l) => $l->where('name', 'like', "%{$v}%"));
                });
            })
            ->latest()
            ->paginate(10);

        return ReportResource::collection($reports);
    }

    public function show(Report $report)
    {
        return new ReportResource($report->load([...$this->with, 'messages.user']));
    }

    // Tunjuk teknisi. Laporan pending otomatis menjadi diproses.
    public function assign(Request $request, Report $report)
    {
    $data = $request->validate([
        'technician_id' => ['nullable', 'exists:users,id'],
        'technician'    => ['nullable', 'string'],
    ]);
    $techId = $data['technician_id'] ?? User::where('name', $data['technician'] ?? '')->value('id');
    abort_unless($techId, 422, 'Teknisi tidak ditemukan.');

        abort_unless(
            User::where('id', $data['technician_id'])->where('role', 'teknisi')->exists(),
            422, 'Pengguna yang dipilih bukan teknisi.'
        );
        abort_if(in_array($report->status, ['selesai', 'ditolak']), 422, 'Laporan sudah ditutup.');

        $report->update([
            'assigned_to' => $techId,
            'assigned_at'   => now(),
            'status'        => $report->status === 'pending' ? 'diproses' : $report->status,
        ]);

        return new ReportResource($report->fresh($this->with));
    }

    // Ubah status + catatan. Ditolak wajib beralasan.
    public function updateStatus(Request $request, Report $report)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,diproses,selesai,ditolak'],
            'note'   => ['required_if:status,ditolak', 'nullable', 'string', 'max:1000'],
        ]);

        $report->update([
            'status'       => $data['status'],
            'admin_note'   => $data['note'] ?? $report->admin_note,
            'completed_at' => $data['status'] === 'selesai' ? now() : null,
        ]);

        return new ReportResource($report->fresh($this->with));
    }

    public function addMessage(Request $request, Report $report)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $comment = $report->comments()->create([
            'user_id' => $request->user()->id,
            'pesan' => $data['message'],
        ]);

        return response()->json($comment->load('user'), 201);
    }
}