<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboardService)
    {
        abort_unless($request->user()->hasPermissionTo('dashboard.view'), 403);

        return view('dashboard', $dashboardService->overview($request->user()));
    }
}
