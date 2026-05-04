<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceLog::with('employee')->orderBy('device_timestamp', 'desc');

        // Filter by Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('device_timestamp', '>=', $request->start_date);
        } else {
            // Default to today if no date is provided
            $request->merge(['start_date' => today()->format('Y-m-d')]);
            $query->whereDate('device_timestamp', '>=', today());
        }

        if ($request->filled('end_date')) {
            $query->whereDate('device_timestamp', '<=', $request->end_date);
        } else {
            $request->merge(['end_date' => today()->format('Y-m-d')]);
            $query->whereDate('device_timestamp', '<=', today());
        }

        // Filter by Employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by Status (Verified / Failed)
        if ($request->filled('status')) {
            $verified = $request->status == 'verified' ? 1 : 0;
            $query->where('verified', $verified);
        }

        $logs = $query->paginate(20)->withQueryString();
        $employees = Employee::orderBy('name')->get();

        // Calculate summaries for the selected period
        $summary = [
            'total' => $logs->total(),
            'verified' => $query->clone()->where('verified', 1)->count(),
            'failed' => $query->clone()->where('verified', 0)->count(),
        ];

        return view('admin.reports.index', compact('logs', 'employees', 'summary'));
    }
}
