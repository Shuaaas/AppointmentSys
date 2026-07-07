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
                <span class="stat-label">Pending approvals</span>
                <span class="stat-icon"><i class="ti ti-clock" aria-hidden="true"></i></span>
            </div>
            <div class="stat-value">{{ $pendingCount }}</div>
            <div class="stat-sub">{{ $pendingCount === 0 ? 'No pending requests' : $pendingCount . ' awaiting review' }}</div>
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

    <div class="dash-grid">
        <div class="dash-card">
            <div class="dash-card-title">Pending account approvals</div>
            @if ($pendingRequests->isEmpty())
                <p class="empty-note">No pending account approvals at this time.</p>
            @else
                <div class="table-card">
                    <div class="tbl-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th style="text-align:right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingRequests as $user)
                                    <tr class="data-row">
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ ucfirst($user->role) }}</td>
                                        <td style="text-align:right;">
                                            <form method="POST" action="{{ route('admin.users.activate', $user) }}" class="approval-form" style="display:inline-block;" data-user-name="{{ $user->name }}" data-action="approve">
                                                @csrf
                                                @method('PATCH')
                                                <button type="button" class="btn btn-sm btn-primary approval-trigger">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="approval-form" style="display:inline-block; margin-left:8px;" data-user-name="{{ $user->name }}" data-action="reject">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger approval-trigger">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

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

    <div id="approval-confirm-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.55); z-index:9999; align-items:center; justify-content:center; padding:20px;">
        <div style="width:min(420px, 100%); background:#fff; border-radius:12px; box-shadow:0 16px 40px rgba(0,0,0,0.2); padding:24px; text-align:center;">
            <h3 id="approval-confirm-title" style="margin:0 0 10px; font-size:20px;">Approve account?</h3>
            <p id="approval-confirm-text" style="margin:0 0 20px; color:#475569;">Are you sure you want to approve this account?</p>
            <div style="display:flex; justify-content:center; gap:10px;">
                <button type="button" class="btn btn-secondary" id="approval-cancel-btn">Cancel</button>
                <button type="button" class="btn btn-primary" id="approval-confirm-btn">Approve</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('approval-confirm-modal');
            const title = document.getElementById('approval-confirm-title');
            const text = document.getElementById('approval-confirm-text');
            const cancelBtn = document.getElementById('approval-cancel-btn');
            const confirmBtn = document.getElementById('approval-confirm-btn');
            let pendingForm = null;

            document.querySelectorAll('.approval-form').forEach(function (form) {
                form.querySelector('.approval-trigger').addEventListener('click', function () {
                    pendingForm = form;
                    const userName = form.dataset.userName || 'this account';
                    const action = form.dataset.action === 'reject' ? 'reject' : 'approve';

                    title.textContent = action === 'reject' ? 'Reject account?' : 'Approve account?';
                    text.textContent = action === 'reject'
                        ? 'Are you sure you want to reject ' + userName + '?'
                        : 'Are you sure you want to approve ' + userName + '?';
                    confirmBtn.textContent = action === 'reject' ? 'Reject' : 'Approve';
                    confirmBtn.className = action === 'reject' ? 'btn btn-danger' : 'btn btn-primary';
                    modal.style.display = 'flex';
                });
            });

            function closeModal() {
                modal.style.display = 'none';
                pendingForm = null;
            }

            cancelBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            confirmBtn.addEventListener('click', function () {
                if (pendingForm) {
                    pendingForm.submit();
                }
            });
        });
    </script>

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

    @if (auth()->user()?->isHr() || auth()->user()?->isManager())
        <div class="dash-grid full-width" style="margin-top: 18px;">
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