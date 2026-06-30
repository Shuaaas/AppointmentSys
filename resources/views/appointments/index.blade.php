@extends('layout.app')

@section('title', 'Appointment data entry')

@section('content')
<div class="action-bar">
    <form class="search-wrap" method="GET" action="{{ route('appointments.index') }}">
        <i class="ti ti-search" aria-hidden="true"></i>
        <input type="text" name="q" value="{{ $search }}" placeholder="Search by name, school, eligibility…"
               onchange="this.form.submit()">
        <input type="hidden" name="date" value="{{ $selectedDate }}">
    </form>

    <form class="date-control" method="GET" action="{{ route('appointments.index') }}">
        <i class="ti ti-calendar" aria-hidden="true"></i>
        <label for="appt-date-select">Date encoded</label>
        <select id="appt-date-select" name="date" onchange="this.form.submit()">
            @forelse ($availableDates as $i => $date)
                <option value="{{ $date }}" {{ $date === $selectedDate ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}{{ $i === 0 ? ' (latest)' : '' }}
                </option>
            @empty
                <option value="">No dates available</option>
            @endforelse
        </select>
        <input type="hidden" name="q" value="{{ $search }}">
    </form>

    <div class="action-bar-right">
        <a href="{{ route('appointments.export') }}" class="btn btn-secondary">
            <i class="ti ti-download" aria-hidden="true"></i> Export CSV
        </a>
        <a href="{{ route('appointments.trash') }}" class="btn btn-secondary" style="color:var(--red);border-color:#e8b4b4">
            <i class="ti ti-trash" aria-hidden="true"></i> Trash
        </a>
        <button type="button" class="btn btn-primary" onclick="openWizard()">
            <i class="ti ti-plus" aria-hidden="true"></i> Add new entry
        </button>
    </div>
</div>

<div class="table-card">
    <div class="tbl-wrap">
        <table>
            <colgroup>
                <col style="width:38px"><col style="width:38px"><col style="width:175px">
                <col style="width:125px"><col style="width:105px"><col style="width:105px">
                <col style="width:105px"><col style="width:80px"><col style="width:130px">
            </colgroup>
            <thead>
                <tr>
                    <th></th><th>#</th><th>Full name</th><th>School / district</th>
                    <th>Nature of appt.</th><th>Status</th><th>Original appt.</th>
                    <th>Eligibility</th><th>Date encoded</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($appointments as $i => $a)
                    <tr class="data-row" id="row-{{ $a->id }}">
                        <td><input type="checkbox" aria-label="Select row"></td>
                        <td style="color:var(--text-muted)">{{ $i + 1 }}</td>
                        <td>
                            <button type="button" class="name-btn" onclick="toggleRow({{ $a->id }}, event)" aria-expanded="false">
                                <span>{{ $a->full_name }}</span>
                                <i class="ti ti-chevron-down" aria-hidden="true"></i>
                            </button>
                            <div class="tn-code">{{ $a->transaction_number }}</div>
                        </td>
                        <td>{{ $a->school_district }}</td>
                        <td><span class="badge badge-teal">{{ $a->nature_of_appointment }}</span></td>
                        <td>
                            @php
                                $statusClass = match($a->employee_status) {
                                    'Permanent' => 'badge-green',
                                    'Temporary' => 'badge-amber',
                                    default => 'badge-blue',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $a->employee_status }}</span>
                        </td>
                        <td>{{ optional($a->date_original_appointment)->format('Y-m-d') ?? '—' }}</td>
                        <td><span class="badge badge-teal">{{ $a->eligibility_type ?? '—' }}</span></td>
                        <td style="font-size:12px;color:var(--text-muted)">{{ $a->encoded_at->format('F j, Y g:i A') }}</td>
                    </tr>
                    <tr class="dropdown-row" id="detail-{{ $a->id }}">
                        <td colspan="9">
                            <div class="drop-panel">
                                <div class="drop-left">
                                    <span class="drop-label">Downloads</span>
                                    <a href="#" class="btn btn-secondary btn-sm"><i class="ti ti-file-text" style="font-size:12px" aria-hidden="true"></i> No.33-B AFA</a>
                                    <a href="#" class="btn btn-secondary btn-sm"><i class="ti ti-checklist" style="font-size:12px" aria-hidden="true"></i> Checklist</a>
                                </div>
                                <div class="drop-right">
                                    <a href="{{ route('appointments.show', $a) }}" class="btn btn-primary btn-sm"><i class="ti ti-eye" style="font-size:12px" aria-hidden="true"></i> View</a>
                                    <button type="button" class="btn btn-success btn-sm" onclick="openEditWizard({{ $a->id }})"><i class="ti ti-edit" style="font-size:12px" aria-hidden="true"></i> Edit</button>
                                    <form action="{{ route('appointments.destroy', $a) }}" method="POST" onsubmit="return false;" id="delete-form-{{ $a->id }}">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-sm" onclick="openDelete({{ $a->id }}, '{{ addslashes($a->full_name) }}')"><i class="ti ti-trash" style="font-size:12px" aria-hidden="true"></i> Delete</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9"><p class="empty-note">No appointments found for this date.</p></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="footer-bar">
        <span>Showing {{ $appointments->count() }} of {{ $appointments->count() }} result{{ $appointments->count() !== 1 ? 's' : '' }}</span>
        <span class="footer-hint"><i class="ti ti-info-circle" aria-hidden="true"></i> Showing the latest encoded date by default — use the dropdown above to view other dates.</span>
    </div>
</div>

@push('modals')
    @include('appointments.partials.wizard-modal')
    @include('appointments.partials.delete-modal')
@endpush

@push('scripts')
<script>
function toggleRow(id, e) {
    e.stopPropagation();
    const row = document.getElementById('detail-' + id);
    const btn = document.querySelector('#row-' + id + ' .name-btn');
    const isOpen = row.classList.contains('open');

    document.querySelectorAll('.dropdown-row.open').forEach(r => r.classList.remove('open'));
    document.querySelectorAll('.name-btn.open').forEach(b => { b.classList.remove('open'); b.setAttribute('aria-expanded', 'false'); });

    if (!isOpen) {
        row.classList.add('open');
        btn.classList.add('open');
        btn.setAttribute('aria-expanded', 'true');
    }
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('.name-btn') && !e.target.closest('.dropdown-row')) {
        document.querySelectorAll('.dropdown-row.open').forEach(r => r.classList.remove('open'));
        document.querySelectorAll('.name-btn.open').forEach(b => { b.classList.remove('open'); b.setAttribute('aria-expanded', 'false'); });
    }
});

function openDelete(id, name) {
    document.getElementById('del-name').textContent = name;
    document.getElementById('overlay-del').classList.add('show');
    document.getElementById('overlay-del').dataset.formId = 'delete-form-' + id;
}
function confirmDelete() {
    const formId = document.getElementById('overlay-del').dataset.formId;
    document.getElementById(formId).submit();
}
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.overlay').forEach(ov => {
    ov.addEventListener('click', function (e) { if (e.target === ov) closeModal(ov.id); });
});
</script>
@endpush
@endsection