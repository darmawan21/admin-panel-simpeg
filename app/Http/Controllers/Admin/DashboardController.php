<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEmployees = Employee::count();
        $todayAttendances = AttendanceLog::whereDate('device_timestamp', today())->count();
        $recentLogs = AttendanceLog::with('employee')->latest('device_timestamp')->take(5)->get();

        return view('admin.dashboard', compact('totalEmployees', 'todayAttendances', 'recentLogs'));
    }
}
