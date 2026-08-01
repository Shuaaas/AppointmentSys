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
        <input type="text" name="q" value="{{ $search }}" placeholder="Search by name, school, eligibility…" onchange="this.form.submit()">
        <select name="user" onchange="this.form.submit()" aria-label="Filter by encoded by">
            <option value="">All Users</option>
            @foreach($hrUsers as $hrUser)
                <option value="{{ $hrUser->id }}" {{ $selectedUser == $hrUser->id ? 'selected' : '' }}>
                    {{ $hrUser->name }}
                </option>
            @endforeach
        </select>
        <input type="hidden" name="from" value="{{ $from }}">
        <input type="hidden" name="to" value="{{ $to }}">
    </form>

    <button type="button" class="btn btn-secondary" id="btn-export-monitoring" onclick="submitMonitoringExport()">
        <i class="ti ti-file-export" aria-hidden="true"></i> <span id="monitoring-export-label">Export Monitoring</span>
    </button>

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

    <form class="date-control" method="GET" action="{{ route('appointments.archive') }}">
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
        <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
        <a href="{{ route('appointments.archive') }}" class="btn btn-sm btn-secondary" style="margin-left:8px">Clear</a>
    </form>
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
                    <th><input type="checkbox" id="select-all-archive" onchange="toggleSelectAll(this)"></th>
                    <th>Full name</th>
                    <th>School / district</th>
                    <th>Nature of appt.</th>
                    <th>Transaction Numbers</th>
                    <th>Date encoded</th>
                    <th>Encoded By</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($appointments as $i => $a)
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
    <div class="footer-bar">
        {{ $appointments->withQueryString()->links('vendor.pagination.pams') }}
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('show');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

function toggleSelectAll(source) {
    document.querySelectorAll('.select-row').forEach(cb => cb.checked = source.checked);
}

document.getElementById('archive-from').addEventListener('change', function() {
    document.getElementById('archive-to').min = this.value;
});
document.getElementById('archive-to').addEventListener('change', function() {
    document.getElementById('archive-from').max = this.value;
});

function submitMonitoringExport() {
    const selected = Array.from(document.querySelectorAll('.select-row:checked')).map(cb => cb.value);
    if (selected.length === 0) {
        alert('Please select at least one record to export.');
        return;
    }
    openModal('overlay-monitoring-confirm');
}

function confirmMonitoringExport() {
    closeModal('overlay-monitoring-confirm');

    const btn = document.getElementById('btn-export-monitoring');
    const label = document.getElementById('monitoring-export-label');
    if (btn) btn.disabled = true;
    if (label) label.textContent = 'Downloading...';

    const selected = Array.from(document.querySelectorAll('.select-row:checked')).map(cb => cb.value);

    const tokenEl = document.querySelector('meta[name="csrf-token"]');
    const token = tokenEl ? tokenEl.getAttribute('content') : '';

    const formData = new FormData();
    formData.append('_token', token);
    selected.forEach(id => formData.append('ids[]', id));

    fetch('{{ route('appointments.archive.exportMonitoring') }}', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error('Server error (' + response.status + '): ' + text.slice(0, 200));
            });
        }
        const disposition = response.headers.get('Content-Disposition');
        return response.blob().then(blob => ({ blob, disposition }));
    })
    .then(({ blob, disposition }) => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        let filename = 'monitoring_export.xlsx';
        if (disposition) {
            const match = disposition.match(/filename\*?=(?:UTF-8''|"?)([^";]+)/i);
            if (match && match[1]) {
                filename = decodeURIComponent(match[1]);
            }
        }
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
    })
    .catch(err => {
        alert('Export failed: ' + err.message);
    })
    .finally(() => {
        setTimeout(() => {
            if (btn) btn.disabled = false;
            if (label) label.textContent = 'Export Monitoring';
        }, 1000);
    });
}
</script>
@endsection
