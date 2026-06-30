<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalActive = Appointment::active()->count();

        $permanentCount = Appointment::active()->where('employee_status', 'Permanent')->count();
        $tempCount = Appointment::active()
            ->whereIn('employee_status', ['Temporary', 'Casual', 'Contractual'])
            ->count();

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $encodedThisMonth = Appointment::active()
                ->whereRaw("strftime('%Y', encoded_at) = ?", [now()->year])
                ->whereRaw("strftime('%m', encoded_at) = ?", [sprintf('%02d', now()->month)])
                ->count();
        } else {
            $encodedThisMonth = Appointment::active()
                ->whereYear('encoded_at', now()->year)
                ->whereMonth('encoded_at', now()->month)
                ->count();
        }

        // Last 6 months trend (encoded counts grouped by month)
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));

        if ($driver === 'sqlite') {
            $monthlyCounts = Appointment::active()
                ->where('encoded_at', '>=', now()->subMonths(5)->startOfMonth())
                ->selectRaw('strftime("%Y", encoded_at) as y, strftime("%m", encoded_at) as m, COUNT(*) as total')
                ->groupByRaw('strftime("%Y", encoded_at), strftime("%m", encoded_at)')
                ->get()
                ->keyBy(fn ($row) => $row->y . '-' . $row->m);
        } else {
            $monthlyCounts = Appointment::active()
                ->where('encoded_at', '>=', now()->subMonths(5)->startOfMonth())
                ->selectRaw('YEAR(encoded_at) as y, MONTH(encoded_at) as m, COUNT(*) as total')
                ->groupBy('y', 'm')
                ->get()
                ->keyBy(fn ($row) => $row->y . '-' . $row->m);
        }

        $trend = $months->map(function ($date) use ($monthlyCounts) {
            $key = $date->year . '-' . $date->month;
            return [
                'label' => $date->format('M'),
                'count' => optional($monthlyCounts->get($key))->total ?? 0,
            ];
        });

        // Status breakdown for the donut/bar widget
        $statusBreakdown = Appointment::active()
            ->select('employee_status', DB::raw('COUNT(*) as total'))
            ->groupBy('employee_status')
            ->pluck('total', 'employee_status');

        // Recently encoded (latest 5)
        $recent = Appointment::active()
            ->orderByDesc('encoded_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', [
            'totalActive'      => $totalActive,
            'permanentCount'   => $permanentCount,
            'tempCount'        => $tempCount,
            'encodedThisMonth' => $encodedThisMonth,
            'trend'            => $trend,
            'statusBreakdown'  => $statusBreakdown,
            'recent'           => $recent,
        ]);
    }
}