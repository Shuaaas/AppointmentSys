@extends('layout.app')

@section('title', 'Dashboard')

@section('content')
<p class="view-subtitle">Overview as of {{ now()->format('F j, Y') }}</p>

@if (auth()->user()?->isAdmin())
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-head">
                <span class="stat-label">Total accounts</span>
                <span class="stat-icon"><i class="ti ti-users" aria-hidden="true"></i></span>
            </div>
            <div class="stat-value">{{ $totalAccounts }}</div>
            <div class="stat-sub">{{ $totalAccounts === 0 ? 'No users yet' : $totalAccounts . ' accounts' }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-head">
                <span class="stat-label">Active accounts</span>
                <span class="stat-icon"><i class="ti ti-check" aria-hidden="true"></i></span>
            </div>
            <div class="stat-value">{{ $activeUsers }}</div>
            <div class="stat-sub">{{ $activeUsers === 0 ? 'No active users' : $activeUsers . ' currently active' }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-head">
                <span class="stat-label">Total appointments</span>
                <span class="stat-icon"><i class="ti ti-file-description" aria-hidden="true"></i></span>
            </div>
            <div class="stat-value">{{ $totalActive }}</div>
            <div class="stat-sub">{{ $totalActive === 0 ? 'No records' : $totalActive . ' active appointments' }}</div>
        </div>
    </div>

    <div class="dash-grid full-width">
        <div class="dash-card">
            <div class="dash-card-title">Active user accounts</div>
            @if ($activeAccounts->isEmpty())
                <p class="empty-note">No active user accounts found.</p>
            @else
                <div class="table-card">
                    <div class="tbl-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($activeAccounts as $user)
                                    <tr class="data-row">
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ ucfirst($user->role) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

@else
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
                <span class="stat-label">Substitute / Provisional</span>
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
        <div class="dash-card encoded-card">
            <div class="dash-card-title">Appointments encoded (last 6 months)</div>
            @php $maxVal = max($trend->max('count'), 1); @endphp
            <div class="bar-chart">
                @foreach ($trend as $point)
                    <div class="bar-col">
                        <div class="bar-stack">
                            @if ($point['count'] > 0)
                                @php
                                    $barHeight = max(16, (int) round(($point['count'] / $maxVal) * 110));
                                @endphp
                                <div class="bar-seg" style="height: {{ $barHeight }}px; min-height: 16px; background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);"></div>
                            @endif
                        </div>
                        <span class="bar-month">{{ $point['label'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="chart-legend">
                <span><span class="legend-dot legend-dot-primary"></span>Appointments encoded</span>
            </div>
        </div>

        <div class="dash-card status-card">
            <div class="dash-card-title">Appointments by status</div>
            @php
                $statusChart = [
                    'Active'      => ['count' => $statusCounts['Active'] ?? 0, 'color' => '#1565C0'],
                    'In Progress' => ['count' => $statusCounts['In Progress'] ?? 0, 'color' => '#C8870B'],
                    'Completed'   => ['count' => $statusCounts['Completed'] ?? 0, 'color' => '#1F7A44'],
                ];
                $statusMax = max(array_merge(array_column($statusChart, 'count'), [1]));
                $statusTotal = array_sum(array_column($statusChart, 'count'));

                $acc = 0;
                $pieSegments = [];
                if ($statusTotal > 0) {
                    foreach ($statusChart as $label => $item) {
                        if ($item['count'] <= 0) continue;
                        $start = round(($acc / $statusTotal) * 100, 2);
                        $acc += $item['count'];
                        $end = round(($acc / $statusTotal) * 100, 2);
                        $pieSegments[] = $item['color'] . ' ' . $start . '% ' . $end . '%';
                    }
                }
                $pieGradient = !empty($pieSegments) ? implode(', ', $pieSegments) : '#e5e7eb 0% 100%';
            @endphp
            @if ($statusTotal === 0)
                <p class="empty-note">No appointments encoded yet.</p>
            @else
                <div class="status-pie-wrap">
                    <div class="status-pie-box">
                        <div class="status-pie" style="background-image: conic-gradient({{ $pieGradient }});"></div>
                        <div class="status-pie-center">
                            <span class="status-pie-total">{{ $statusTotal }}</span>
                            <span class="status-pie-label">Total</span>
                        </div>
                    </div>

                    <div class="status-list">
                        @foreach ($statusChart as $label => $item)
                            @php
                                $fillWidth = $statusMax > 0 ? (int) round(($item['count'] / $statusMax) * 100) : 0;
                            @endphp
                            <div class="status-row">
                                <span class="status-name">{{ $label }}</span>
                                <div class="status-bar-track">
                                    <div class="status-bar-fill" style="width: {{ $fillWidth }}%; background-color: {{ $item['color'] }};"></div>
                                </div>
                                <span class="status-count">{{ $item['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="chart-legend chart-legend-status">
                    <span><span class="legend-dot legend-dot-active"></span>Active</span>
                    <span><span class="legend-dot legend-dot-progress"></span>In Progress</span>
                    <span><span class="legend-dot legend-dot-completed"></span>Completed</span>
                </div>
            @endif
        </div>
    </div>

        @if (auth()->user()?->isHr())
        <div class="dash-grid full-width recent-grid">
            <div class="dash-card">
                <div class="dash-card-title">Recently encoded</div>
                @if ($recent->isEmpty())
                    <p class="empty-note">No recent appointments yet.</p>
                @else
                    <div class="table-card">
                        <div class="tbl-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Encoded</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recent as $appointment)
                                        <tr class="data-row">
                                            <td>{{ $appointment->full_name }}</td>
                                            <td>{{ $appointment->encoded_at ? $appointment->encoded_at->format('M d, Y') : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

@endif
@endsection

@push('scripts')
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush