@extends('layout.app')

@section('title', 'Transaction Numbers')

@section('content')
<div class="stat-grid" style="grid-template-columns:repeat(1,minmax(0,1fr))">
    <div class="stat-card">
        <div class="stat-head">
            <span class="stat-label">Needs Transaction Number</span>
            <span class="stat-icon"><i class="ti ti-file-text" aria-hidden="true"></i></span>
        </div>
        <div class="stat-value">{{ $needsTNCount }}</div>
    </div>
</div>

<div class="action-bar">
    <form class="search-wrap" method="GET" action="{{ route('appointments.transactionNumbers') }}">
        <i class="ti ti-search" aria-hidden="true"></i>
        <input type="text" name="q" value="{{ $search }}" placeholder="Search by name, school, eligibility…" onchange="this.form.submit()">
        <input type="hidden" name="date" value="{{ $selectedDate }}">
    </form>

    <form class="date-control" method="GET" action="{{ route('appointments.transactionNumbers') }}">
        <i class="ti ti-calendar" aria-hidden="true"></i>
        <label for="tn-date-select">Date encoded</label>
        <select id="tn-date-select" name="date" onchange="this.form.submit()">
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
</div>

<div class="table-card records-tn-card">
    <div class="tbl-wrap">
        <table class="records-table">
            <colgroup>
                <col style="width:42px">
                <col style="width:260px">
                <col style="width:170px">
                <col style="width:240px">
                <col style="width:140px">
                <col style="width:120px">
                <col>
            </colgroup>
            <thead>
                <tr>
                    <th>#</th>
                    <th>FULL NAME</th>
                    <th>ITEM NO.</th>
                    <th>SCHOOL / DIVISION</th>
                    <th>NATURE OF APPT.</th>
                    <th>DATE ENCODED</th>
                    <th>TRANSACTION NUMBER</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($appointments as $i => $a)
                    <tr class="data-row">
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <div class="name-row" style="display:inline-flex;align-items:center;gap:8px;">
                                <span class="name-text">{{ $a->full_name }}</span>
                            </div>
                        </td>
                        <td>{{ $a->plantilla_item_number ?: '—' }}</td>
                        <td>{{ $a->school_district ?: '—' }}</td>
                        <td><span class="badge badge-teal">{{ $a->nature_of_appointment ?: '—' }}</span></td>
                        <td>{{ optional($a->encoded_at)->format('M j') }}</td>
                        <td>
                            <form method="POST" action="{{ route('appointments.updateTransactionNumber', $a) }}" class="tn-form" id="tn-form-{{ $a->id }}">
                                @csrf
                                @method('PATCH')
                                <div class="tn-input-row">
                                    <input type="text" name="transaction_number" value="{{ old('transaction_number', $a->transaction_number) }}" placeholder="Enter TN..." style="height:1.875rem">
                                    <button type="button" class="btn btn-sm btn-blue" onclick="handleTnSubmit({{ $a->id }})">Save</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr class="no-rows">
                        <td colspan="7" style="border-bottom:0;padding:18px 12px;">
                            <p class="empty-note" style="margin:0;">All appointments have transaction numbers.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="footer-bar">
        <span>Showing {{ $appointments->count() }} of {{ $appointments->count() }} result{{ $appointments->count() !== 1 ? 's' : '' }}</span>
        <span class="footer-hint"><i class="ti ti-info-circle" aria-hidden="true"></i> Enter or update the Transaction Number for your own appointments here.</span>
    </div>
</div>

<div class="overlay" id="overlay-tn-error">
    <div class="modal" style="max-width:420px">
        <div class="modal-head">
            <span class="modal-title">Invalid Transaction Number</span>
            <button type="button" class="modal-close" onclick="closeModal('overlay-tn-error')">&times;</button>
        </div>
        <div class="modal-body">
            <p id="tn-error-message">Please enter a valid Transaction Number before saving.</p>
        </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-blue" onclick="closeModal('overlay-tn-error')">Back</button>
            </div>
    </div>
</div>

<div class="overlay" id="overlay-tn-confirm">
    <div class="modal" style="max-width:420px">
        <div class="modal-head">
            <span class="modal-title">Confirm Transaction Number</span>
            <button type="button" class="modal-close" onclick="closeModal('overlay-tn-confirm')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to save this Transaction Number? The record will be moved to the Archive page.</p>
        </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('overlay-tn-confirm')">Cancel</button>
                <button type="button" class="btn btn-blue" id="btn-confirm-tn-save" onclick="confirmTnSave()">Confirm</button>
            </div>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('show');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

let currentTnFormId = null;

function handleTnSubmit(appointmentId) {
    const form = document.getElementById('tn-form-' + appointmentId);
    if (!form) return;

    const input = form.querySelector('input[name="transaction_number"]');
    const errorMsg = document.getElementById('tn-error-message');

    if (!input || !input.value.trim()) {
        if (errorMsg) errorMsg.textContent = 'Please enter a valid Transaction Number before saving.';
        openModal('overlay-tn-error');
        return;
    }

    const tn = encodeURIComponent(input.value.trim());

    fetch('{{ route("appointments.checkTransactionNumber") }}?tn=' + tn + '&id=' + appointmentId, {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.exists) {
            if (errorMsg) errorMsg.textContent = 'This Transaction Number already exists. Please enter a different one.';
            openModal('overlay-tn-error');
        } else {
            currentTnFormId = appointmentId;
            openModal('overlay-tn-confirm');
        }
    })
    .catch(() => {
        currentTnFormId = appointmentId;
        openModal('overlay-tn-confirm');
    });
}

function confirmTnSave() {
    if (!currentTnFormId) return;

    const form = document.getElementById('tn-form-' + currentTnFormId);
    if (form) {
        form.submit();
    }
}
</script>
@endsection
