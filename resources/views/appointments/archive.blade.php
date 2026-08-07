@extends('layout.app')

@section('title', 'Archive')

@section('content')

    @if (session('success'))
        <div class="alert alert-success" role="alert" style="margin-bottom:16px">
            <i class="ti ti-circle-check" aria-hidden="true"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="action-bar">
        <form class="search-wrap" method="GET" action="{{ route('appointments.archive') }}">
            <i class="ti ti-search" aria-hidden="true"></i>
            <input type="text" name="q" value="{{ $search }}" placeholder="Search by name, school, eligibility…" class="js-autosubmit">
            <select name="user" class="js-autosubmit" aria-label="Filter by encoded by">
                <option value="">All Users</option>
                @foreach($hrUsers as $hrUser)
                    <option value="{{ $hrUser->id }}" {{ $selectedUser == $hrUser->id ? 'selected' : '' }}>
                        {{ $hrUser->name }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="from" value="{{ $from }}">
            <input type="hidden" name="to" value="{{ $to }}">
            @if ($usingMonthNav && $activeMonth)
                <input type="hidden" name="month" value="{{ $activeMonth->format('Y-m') }}">
            @endif
        </form>

        <button type="button" class="btn btn-secondary" id="btn-export-monitoring" onclick="submitMonitoringExport()">
            <i class="ti ti-file-export" aria-hidden="true"></i> <span id="monitoring-export-label">Export Monitoring</span>
        </button>

        <form class="date-control date-control-range" method="GET" action="{{ route('appointments.archive') }}">
            <div class="date-range">
                <i class="ti ti-calendar" aria-hidden="true"></i>
                <label for="archive-from">From</label>
                <input type="date" id="archive-from" name="from" value="{{ $from }}">
            </div>
            <div class="date-range">
                <i class="ti ti-calendar" aria-hidden="true"></i>
                <label for="archive-to">To</label>
                <input type="date" id="archive-to" name="to" value="{{ $to }}">
            </div>
            <input type="hidden" name="q" value="{{ $search }}">
            <input type="hidden" name="user" value="{{ $selectedUser ?? '' }}">
            <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
            <a href="{{ route('appointments.archive') }}" class="btn btn-sm btn-secondary">Clear</a>
        </form>
    </div>

    <div class="overlay" id="overlay-monitoring-confirm">
        <div class="modal" style="max-width:420px">
            <div class="modal-head">
                <span class="modal-title">Confirm Export</span>
                <button type="button" class="modal-close" onclick="closeModal('overlay-monitoring-confirm')">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to export the monitoring data for the selected record(s)?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('overlay-monitoring-confirm')">Cancel</button>
                <button type="button" class="btn btn-blue" onclick="confirmMonitoringExport()">Export</button>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="tbl-wrap">
            <table>
                <colgroup>
                    <col style="width:38px"><col style="width:240px">
                    <col style="width:170px"><col style="width:150px">
                    <col style="width:200px"><col style="width:140px">
                    <col style="width:160px">
                </colgroup>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all-archive"></th>
                        <th>Full name</th>
                        <th>School / district</th>
                        <th>Nature of appt.</th>
                        <th>Transaction Numbers</th>
                        <th>Date encoded</th>
                        <th>Encoded By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appointments as $a)
                        <tr class="data-row" id="row-{{ $a->id }}">
                            <td><input type="checkbox" class="select-row" name="ids[]" value="{{ $a->id }}"></td>
                            <td>
                                <div class="name-text">{{ $a->full_name }}</div>
                                <div class="small-text">{{ $a->position_title }}</div>
                            </td>
                            <td>{{ $a->school_district ?: '—' }}</td>
                            <td><span class="badge badge-teal">{{ $a->nature_of_appointment ?: '—' }}</span></td>
                            <td><span class="badge badge-green">{{ $a->transaction_number ?: '—' }}</span></td>
                            <td style="font-size:12px;color:var(--text-muted)">{{ optional($a->encoded_at)->format('F j, Y') }}</td>
                            <td style="font-size:12px;color:var(--text-muted)">{{ $a->owner->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr class="no-rows">
                            <td colspan="7" style="border-bottom:0;padding:18px 12px;">
                                <p class="empty-note" style="margin:0;">No archived (completed) appointments found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="footer-bar history-footer-bar">
            @if ($usingMonthNav)
                {{-- Month-based navigation --}}
                <span class="pams-count">
                    {{ $appointments->count() }} record{{ $appointments->count() !== 1 ? 's' : '' }} in {{ $monthLabel }}
                </span>
                <nav class="month-nav" aria-label="Month navigation">
                    <a href="{{ $prevMonthUrl }}"
                       class="pams-btn month-nav-btn"
                       aria-label="Previous month ({{ \Carbon\Carbon::parse($activeMonth)->subMonth()->format('F Y') }})"
                       title="{{ \Carbon\Carbon::parse($activeMonth)->subMonth()->format('F Y') }}">
                        <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    </a>

                    <span class="month-nav-label">{{ $monthLabel }}</span>

                    @if ($nextMonthUrl)
                        <a href="{{ $nextMonthUrl }}"
                           class="pams-btn month-nav-btn"
                           aria-label="Next month ({{ \Carbon\Carbon::parse($activeMonth)->addMonth()->format('F Y') }})"
                           title="{{ \Carbon\Carbon::parse($activeMonth)->addMonth()->format('F Y') }}">
                            <i class="bi bi-chevron-right" aria-hidden="true"></i>
                        </a>
                    @else
                        <button class="pams-btn month-nav-btn" disabled aria-label="No future months" title="You are viewing the current month">
                            <i class="bi bi-chevron-right" aria-hidden="true"></i>
                        </button>
                    @endif
                </nav>
            @else
                {{-- Date-range filter is active — show a simple count --}}
                <span class="pams-count">
                    {{ $appointments->count() }} record{{ $appointments->count() !== 1 ? 's' : '' }} found
                </span>
            @endif
        </div>
    </div>

@push('scripts')
<script>
window._pamsArchiveExportUrl = '{{ route('appointments.archive.exportMonitoring') }}';
</script>
<script src="{{ asset('js/appointments-archive.js') }}"></script>
@endpush
@endsection
