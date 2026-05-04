<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query();
        
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
        }

        $employees = $query->latest()->paginate(15);
        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('admin.employees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|unique:employees',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:employees',
            'password' => 'required|string|min:6|confirmed',
            'role' => ['required', Rule::in(['employee', 'supervisor', 'admin'])],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['enrollment_status'] = 'pending';
        $validated['is_active'] = true;

        Employee::create($validated);

        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee)
    {
        return view('admin.employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'string', Rule::unique('employees')->ignore($employee->id)],
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('employees')->ignore($employee->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role' => ['required', Rule::in(['employee', 'supervisor', 'admin'])],
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active');

        $employee->update($validated);

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->id === auth()->user()->id) {
            return redirect()->route('admin.employees.index')->with('error', 'You cannot delete yourself.');
        }

        $employee->delete();
        return redirect()->route('admin.employees.index')->with('success', 'Employee deleted successfully.');
    }
}
