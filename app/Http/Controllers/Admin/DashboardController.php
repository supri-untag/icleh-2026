<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use App\Services\ConferenceContext;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private ConferenceContext $conferenceContext,
        private AdminDashboardService $adminDashboardService,
    ) {}

    public function __invoke(): View
    {
        $conference = $this->conferenceContext->current();

        return view('admin.dashboard', [
            'conference' => $conference,
            'summary' => $this->adminDashboardService->summary($conference),
        ]);
    }
}
