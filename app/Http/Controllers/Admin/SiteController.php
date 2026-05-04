<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkSite;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiteController extends Controller
{
    public function index()
    {
        $sites = WorkSite::latest()->paginate(15);
        return view('admin.sites.index', compact('sites'));
    }

    public function create()
    {
        return view('admin.sites.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10',
            'policy' => ['required', Rule::in(['warn', 'reject'])],
        ]);

        WorkSite::create($validated);

        return redirect()->route('admin.sites.index')->with('success', 'Work site created successfully.');
    }

    public function edit(WorkSite $site)
    {
        return view('admin.sites.edit', compact('site'));
    }

    public function update(Request $request, WorkSite $site)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10',
            'policy' => ['required', Rule::in(['warn', 'reject'])],
        ]);

        $site->update($validated);

        return redirect()->route('admin.sites.index')->with('success', 'Work site updated successfully.');
    }

    public function destroy(WorkSite $site)
    {
        $site->delete();
        return redirect()->route('admin.sites.index')->with('success', 'Work site deleted successfully.');
    }
}
