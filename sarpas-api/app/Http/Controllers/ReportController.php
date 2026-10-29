<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    private const MAX_REPORTS_PER_DAY = 2;

    public function index(Request $request)
    {
        $reports = $request->user()
            ->reports()
            ->with(['location.area', 'facilityCategory', 'teknisi', 'attachments'])
            ->latest()
            ->paginate(10);

        return ReportResource::collection($reports);
    }

    public function store(StoreReportRequest $request)
    {
        $user = $request->user();

        $todayCount = $user->reports()->whereDate('created_at', today())->count();

        if ($todayCount >= self::MAX_REPORTS_PER_DAY) {
            return response()->json([
                'message' => 'Batas pengaduan tercapai. Maksimal ' . self::MAX_REPORTS_PER_DAY . ' laporan per hari.',
            ], 429);
        }

        $report = DB::transaction(function () use ($request, $user) {
            $report = $user->reports()->create($request->safe()->only([
                'location_id', 'facility_category_id', 'deskripsi', 'urgensi',
            ]));

            foreach ($request->file('photos', []) as $photo) {
                $report->attachments()->create([
                    'path' => $photo->store('reports', 'public'),
                    'tipe' => 'bukti',
                ]);
            }

            return $report;
        });

        $report->load(['location.area', 'facilityCategory', 'teknisi', 'attachments']);

        return (new ReportResource($report))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Report $report)
    {
        Gate::authorize('view', $report);

        $report->load(['location.area', 'facilityCategory', 'teknisi', 'attachments']);

        return new ReportResource($report);
    }
    public function quota(Request $request)
    {
        $limit = 2;
        $terpakai = Report::where('user_id', $request->user()->id)
            ->whereDate('created_at', today())
            ->count();

        return response()->json([
            'limit'    => $limit,
            'terpakai' => $terpakai,
            'sisa'     => max(0, $limit - $terpakai),
        ]);
    }

    public function addMessage(Request $request, Report $report)
    {
        Gate::authorize('view', $report);

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
