<?php

namespace App\Services\Dashboard;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Provides all aggregated data required by the Dashboard view.
 * Extracted from DashboardController to keep the controller thin and
 * make these queries independently testable.
 */
class DashboardService
{
    /**
     * Collect all data required by the dashboard view.
     * Admin users receive additional account management stats.
     * HR users are scoped to their own appointments.
     *
     * @return array<string, mixed>
     */
    public function getData(User $user): array
    {
        $data = array_merge(
            $this->getAppointmentStats($user),
            ['trend' => $this->getMonthlyTrend($user)],
            ['statusBreakdown' => $this->getStatusBreakdown($user)],
            ['statusCounts' => $this->getRecordStateCounts($user)],
            ['recent' => $this->getRecentAppointments($user)],
        );

        if ($user->isAdmin()) {
            $data = array_merge($data, $this->getAdminData());
        }

        return $data;
    }

    private function scopedQuery(User $user): \Illuminate\Database\Eloquent\Builder
    {
        $query = Appointment::query();

        if ($user->isHr()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    /**
     * Core appointment summary counts shown on all dashboard cards.
     *
     * @return array{totalActive: int, permanentCount: int, tempCount: int, encodedThisMonth: int}
     */
    public function getAppointmentStats(User $user): array
    {
        $baseQuery = $this->scopedQuery($user)->active();

        return [
            'totalActive'      => $baseQuery->count(),
            'permanentCount'   => (clone $baseQuery)->where('employee_status', 'Permanent')->count(),
            'tempCount'        => (clone $baseQuery)->whereIn('employee_status', ['Substitute', 'Provisional'])->count(),
            'encodedThisMonth' => $this->countEncodedThisMonth($user),
        ];
    }

    /**
     * Count appointments encoded in the current calendar month.
     * Uses a driver-aware query to support both SQLite (dev) and MySQL (prod).
     */
    public function countEncodedThisMonth(User $user): int
    {
        $query = $this->scopedQuery($user)->active();

        if (DB::getDriverName() === 'sqlite') {
            return $query
                ->whereRaw("strftime('%Y', encoded_at) = ?", [now()->year])
                ->whereRaw("strftime('%m', encoded_at) = ?", [sprintf('%02d', now()->month)])
                ->count();
        }

        return $query
            ->whereYear('encoded_at', now()->year)
            ->whereMonth('encoded_at', now()->month)
            ->count();
    }

    /**
     * Last 6 months appointment trend for the chart widget.
     *
     * @return Collection<int, array{label: string, count: int}>
     */
    public function getMonthlyTrend(User $user): Collection
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));
        $baseQuery = $this->scopedQuery($user)->active();

        if (DB::getDriverName() === 'sqlite') {
            $monthlyCounts = $baseQuery
                ->where('encoded_at', '>=', now()->subMonths(5)->startOfMonth())
                ->selectRaw('strftime("%Y", encoded_at) as y, strftime("%m", encoded_at) as m, COUNT(*) as total')
                ->groupByRaw('strftime("%Y", encoded_at), strftime("%m", encoded_at)')
                ->get()
                ->keyBy(fn ($row) => $row->y . '-' . $row->m);
        } else {
            $monthlyCounts = $baseQuery
                ->where('encoded_at', '>=', now()->subMonths(5)->startOfMonth())
                ->selectRaw('YEAR(encoded_at) as y, MONTH(encoded_at) as m, COUNT(*) as total')
                ->groupBy('y', 'm')
                ->get()
                ->keyBy(fn ($row) => $row->y . '-' . $row->m);
        }

        return $months->map(function ($date) use ($monthlyCounts) {
            $key = $date->year . '-' . $date->month;

            return [
                'label' => $date->format('M'),
                'count' => optional($monthlyCounts->get($key))->total ?? 0,
            ];
        });
    }

    /**
     * Appointment counts grouped by employee_status for the donut/bar widget.
     *
     * @return Collection<string, int>
     */
    public function getStatusBreakdown(User $user): Collection
    {
        return $this->scopedQuery($user)
            ->active()
            ->select('employee_status', DB::raw('COUNT(*) as total'))
            ->groupBy('employee_status')
            ->pluck('total', 'employee_status');
    }

    /**
     * Appointment counts by record_state (Active / In Progress / Completed).
     *
     * @return array<string, int>
     */
    public function getRecordStateCounts(User $user): array
    {
        $baseQuery = $this->scopedQuery($user);

        return [
            'Active'      => (clone $baseQuery)->whereIn('record_state', ['active', 'new'])->count(),
            'In Progress' => (clone $baseQuery)->where('record_state', 'in_progress')->count(),
            'Completed'   => (clone $baseQuery)->where('record_state', 'completed')->count(),
        ];
    }

    /**
     * The five most recently encoded active appointments.
     *
     * @return Collection<int, Appointment>
     */
    public function getRecentAppointments(User $user): Collection
    {
        return $this->scopedQuery($user)
            ->active()
            ->orderByDesc('encoded_at')
            ->limit(5)
            ->get();
    }

    /**
     * Extra data shown only to Admin users: account counts and pending requests.
     *
     * @return array<string, mixed>
     */
    public function getAdminData(): array
    {
        return [
            'totalAccounts'   => User::count(),
            'activeUsers'     => User::where('is_active', true)->count(),
            'pendingCount'    => User::where('is_active', false)->whereNotNull('requested_role')->count(),
            'pendingRequests' => User::where('is_active', false)
                                    ->whereNotNull('requested_role')
                                    ->orderByDesc('created_at')
                                    ->get(),
            'activeAccounts'  => User::where('is_active', true)->orderBy('name')->get(),
        ];
    }
}
