<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    /**
     * Display the main dashboard for HR and Admin users.
     * Data aggregation is fully delegated to DashboardService.
     */
    public function index(): View
    {
        $viewData = $this->dashboardService->getData(auth()->user());

        return view('dashboard.index', $viewData);
    }
}