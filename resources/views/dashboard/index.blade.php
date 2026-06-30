@extends('layout.app')

@section('title', 'Dashboard')

@section('content')
<p class="view-subtitle">Appointment overview as of {{ now()->format('F j, Y') }}</p>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-head">
            <span class="stat-label">Total appointments</span>
            <span class="stat-icon"><i class="ti ti-file-description" aria-hidden="true"></i></span>
        </div>
        <div class="stat-value">{{ $totalActive }}</div>
        <div class="stat-sub">{{ $totalActive === 0 ? 'No records yet' : $totalActive . ' active record' . ($totalActive !== 1 ? 's' : '') }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-head">
            <span class="stat-label">Permanent</span>
            <span class="stat-icon"><i class="ti ti-shield-check" aria-hidden="true"></i></span>
        </div>
        <div class="stat-value">{{ $permanentCount }}</div>
        <div class="stat-sub">{{ $totalActive ? round($permanentCount / $totalActive * 100) . '% of total' : '—' }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-head">
            <span class="stat-label">Temporary / casual</span>
            <span class="stat-icon"><i class="ti ti-clock-hour-4" aria-hidden="true"></i></span>
        </div>
        <div class="stat-value">{{ $tempCount }}</div>
        <div class="stat-sub">{{ $totalActive ? round($tempCount / $totalActive * 100) . '% of total' : '—' }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-head">
            <span class="stat-label">Encoded this month</span>
            <span class="stat-icon"><i class="ti ti-calendar-plus" aria-hidden="true"></i></span>
        </div>
        <div class="stat-value">{{ $encodedThisMonth }}</div>
        <div class="stat-sub up">{{ now()->format('F Y') }}</div>
    </div>
</div>

<div class="dash-grid">
    <div class="dash-card">
        <div class="dash-card-title">Appointments encoded (last 6 months)</div>
        @php $maxVal = max($trend->max('count'), 1); @endphp
        <div class="bar-chart">
            @foreach ($trend as $point)
                <div class="bar-col">
                    <div class="bar-stack">
                        <div class="bar-seg" style="height: {{ round($point['count'] / $maxVal * 150) }}px"></div>
                    </div>
                    <span class="bar-month">{{ $point['label'] }}</span>
                </div>
            @endforeach
        </div>
        <div class="chart-legend">
            <span><span class="legend-dot" style="background:var(--accent)"></span>Appointments encoded</span>
        </div>
    </div>

    <div class="dash-card">
        <div class="dash-card-title">By employment status</div>
        @if ($statusBreakdown->isEmpty())
            <p class="empty-note">No appointments encoded yet.</p>
        @else
            @php
                $maxCount = $statusBreakdown->max();
                $colors = [
                    'Permanent' => 'var(--green)',
                    'Temporary' => 'var(--amber)',
                    'Casual' => '#7a8aa0',
                    'Contractual' => 'var(--accent)',
                    'Coterminous' => '#8a6dca',
                ];
            @endphp
            <div class="status-list">
                @foreach ($statusBreakdown as $status => $count)
                    <div class="status-row">
                        <span class="status-name">{{ $status }}</span>
                        <div class="status-bar-track">
                            <div class="status-bar-fill" style="width: {{ round($count / $maxCount * 100) }}%; background: {{ $colors[$status] ?? 'var(--accent)' }}"></div>
                        </div>
                        <span class="status-count">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div class="dash-grid" style="margin-top:18px; grid-template-columns: 1fr;">
    <div class="dash-card">
        <div class="dash-card-title">Recently encoded</div>
        @if ($recent->isEmpty())
            <p class="empty-note">No appointments have been encoded yet. Add a new entry to get started.</p>
        @else
            <div class="recent-list">
                @foreach ($recent as $r)
                    <div class="recent-item">
                        <div>
                            <div class="recent-name">{{ $r->full_name }}</div>
                            <div class="recent-meta">{{ $r->position_title }} &middot; {{ $r->school_district }}</div>
                        </div>
                        <div class="recent-meta">{{ $r->encoded_at->format('F j, Y g:i A') }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection