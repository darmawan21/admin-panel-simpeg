<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkSite;
use Illuminate\Http\JsonResponse;

class SiteController extends Controller
{
    /**
     * GET /sites
     * Return all active work sites with geofence data.
     */
    public function index(): JsonResponse
    {
        $sites = WorkSite::where('is_active', true)
            ->select(['id', 'name', 'address', 'latitude', 'longitude', 'geofence_radius', 'geofence_policy'])
            ->get();

        return response()->json([
            'data' => $sites,
        ]);
    }
}
