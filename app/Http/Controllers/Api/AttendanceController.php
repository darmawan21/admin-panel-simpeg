<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClockInRequest;
use App\Models\AttendanceLog;
use App\Models\SyncError;
use App\Services\FaceVerificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected FaceVerificationService $faceService;

    public function __construct(FaceVerificationService $faceService)
    {
        $this->faceService = $faceService;
    }

    /**
     * POST /attendance/clock-in
     * Process a clock-in with face embedding + GPS.
     */
    public function clockIn(ClockInRequest $request): JsonResponse
    {
        return $this->processAttendance($request, 'clock_in');
    }

    /**
     * POST /attendance/clock-out
     * Process a clock-out with face embedding + GPS.
     */
    public function clockOut(ClockInRequest $request): JsonResponse
    {
        return $this->processAttendance($request, 'clock_out');
    }

    /**
     * Common handler for clock-in / clock-out.
     */
    private function processAttendance(ClockInRequest $request, string $type): JsonResponse
    {
        $employee = auth()->user();

        // Verify face against stored embedding
        $verified = false;
        $score = 0.0;
        $extractedEmbedding = null;

        // Dev mode: skip face verification for testing
        $devMode = config('app.face_dev_mode', false);

        $faceEmbedding = $employee->faceEmbedding;
        if ($devMode) {
            // In dev mode, always mark as verified
            $verified = true;
            $score = 1.0;
        } elseif ($faceEmbedding && $faceEmbedding->enrollment_status === 'enrolled') {
            if ($request->has('face_image')) {
                // New flow: mobile sends face image → Python service extracts & verifies
                $imageBytes = base64_decode($request->input('face_image'));
                if ($imageBytes === false) {
                    return response()->json([
                        'verified' => false,
                        'message' => 'Invalid face image data.',
                    ], 422);
                }

                $result = $this->faceService->verifyWithImage(
                    $imageBytes,
                    $faceEmbedding->embedding
                );
                $verified = $result['verified'];
                $score = $result['score'];
                $extractedEmbedding = $result['embedding'];
            } else {
                // Legacy flow: mobile sends pre-computed embedding
                $result = $this->faceService->verify(
                    $request->input('embedding'),
                    $faceEmbedding->embedding
                );
                $verified = $result['verified'];
                $score = $result['score'];
            }
        }

        \Log::info('Attendance Face Verification', [
            'type' => $type,
            'method' => $request->has('face_image') ? 'image' : 'embedding',
            'devMode' => $devMode,
            'verified' => $verified,
            'score' => $score,
        ]);

        // Reject if face verification failed (unless in dev mode)
        if (!$verified && !$devMode) {
            \Log::warning('Attendance rejected: face verification failed', [
                'employee_id' => $employee->id,
                'type' => $type,
                'score' => $score,
            ]);
            return response()->json([
                'verified' => false,
                'score' => $score,
                'message' => 'Face verification failed. Please try again.',
            ], 403);
        }

        // Use the extracted embedding from Python service, or the one from request
        $embeddingToStore = $extractedEmbedding ?? $request->input('embedding');

        // Create attendance record
        $log = AttendanceLog::create([
            'employee_id' => $employee->id,
            'type' => $type,
            'embedding' => $embeddingToStore,
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'accuracy' => $request->input('accuracy'),
            'device_timestamp' => Carbon::parse($request->input('timestamp')),
            'verified' => $verified,
            'verification_score' => $score,
            'synced_from_offline' => false,
        ]);

        return response()->json([
            'record_id' => $log->id,
            'type' => $type,
            'verified' => $verified,
            'score' => $score,
            'device_timestamp' => $log->device_timestamp->toIso8601String(),
            'created_at' => $log->created_at->toIso8601String(),
        ], 201);
    }

    /**
     * POST /attendance/sync
     * Batch process offline attendance records.
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'records' => 'required|array|min:1|max:50',
            'records.*.embedding' => 'required|array|min:192|max:192',
            'records.*.latitude' => 'required|numeric',
            'records.*.longitude' => 'required|numeric',
            'records.*.accuracy' => 'required|numeric',
            'records.*.timestamp' => 'required|date',
            'records.*.type' => 'sometimes|string|in:clock_in,clock_out',
        ]);

        $employee = auth()->user();
        $results = ['synced' => 0, 'failed' => 0, 'errors' => []];

        $faceEmbedding = $employee->faceEmbedding;
        $devMode = config('app.face_dev_mode', false);

        foreach ($request->input('records') as $index => $record) {
            try {
                $verified = false;
                $score = 0.0;

                if ($devMode) {
                    $verified = true;
                    $score = 1.0;
                } elseif ($faceEmbedding && $faceEmbedding->enrollment_status === 'enrolled') {
                    $result = $this->faceService->verify(
                        $record['embedding'],
                        $faceEmbedding->embedding
                    );
                    $verified = $result['verified'];
                    $score = $result['score'];
                }

                // Skip records that fail verification (unless dev mode)
                if (!$verified && !$devMode) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'index' => $index,
                        'message' => 'Face verification failed (score: ' . $score . ')',
                    ];
                    continue;
                }

                AttendanceLog::create([
                    'employee_id' => $employee->id,
                    'type' => $record['type'] ?? 'clock_in',
                    'embedding' => $record['embedding'],
                    'latitude' => $record['latitude'],
                    'longitude' => $record['longitude'],
                    'accuracy' => $record['accuracy'],
                    'device_timestamp' => Carbon::parse($record['timestamp']),
                    'verified' => $verified,
                    'verification_score' => $score,
                    'synced_from_offline' => true,
                ]);

                $results['synced']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'index' => $index,
                    'message' => $e->getMessage(),
                ];

                // Log sync error
                SyncError::create([
                    'employee_id' => $employee->id,
                    'error_type' => 'sync_failure',
                    'error_message' => $e->getMessage(),
                    'payload' => $record,
                ]);
            }
        }

        return response()->json($results);
    }

    /**
     * GET /attendance/history
     * Paginated attendance history with date filters.
     */
    public function history(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'sometimes|date',
            'to' => 'sometimes|date',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $employee = auth()->user();
        $query = AttendanceLog::byEmployee($employee->id)
            ->orderBy('device_timestamp', 'desc');

        if ($request->has('from')) {
            $query->where('device_timestamp', '>=', Carbon::parse($request->input('from')));
        }

        if ($request->has('to')) {
            $query->where('device_timestamp', '<=', Carbon::parse($request->input('to')));
        }

        $perPage = $request->input('per_page', 20);
        $logs = $query->paginate($perPage);

        return response()->json([
            'data' => $logs->items(),
            'current_page' => $logs->currentPage(),
            'last_page' => $logs->lastPage(),
            'per_page' => $logs->perPage(),
            'total' => $logs->total(),
        ]);
    }

    /**
     * GET /attendance/today
     * Get today's clock-in and clock-out status.
     */
    public function today(): JsonResponse
    {
        $employee = auth()->user();

        $clockIn = AttendanceLog::byEmployee($employee->id)
            ->today()
            ->clockIn()
            ->orderBy('device_timestamp', 'asc')
            ->first();

        $clockOut = AttendanceLog::byEmployee($employee->id)
            ->today()
            ->clockOut()
            ->orderBy('device_timestamp', 'desc')
            ->first();

        return response()->json([
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'has_clocked_in' => $clockIn !== null,
            'has_clocked_out' => $clockOut !== null,
        ]);
    }
}
