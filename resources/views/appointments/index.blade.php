@extends('layout.app')

@section('title')
    Appointment Data
@endsection

@section('content')

<div class="action-bar">
    <div class="toolbar-search-group">
        <form class="search-wrap" method="GET" action="{{ route('appointments.index') }}">
            <i class="ti ti-search" aria-hidden="true"></i>
            <input type="text" name="q" value="{{ $search }}" placeholder="Search by name, school, eligibility…"
                   class="js-autosubmit">
            <select name="user" class="js-autosubmit" aria-label="Filter by encoded by">
                <option value="">All Users</option>
                @foreach($hrUsers as $hrUser)
                    <option value="{{ $hrUser->id }}" {{ $selectedUser == $hrUser->id ? 'selected' : '' }}>
                        {{ $hrUser->name }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="date" value="{{ $selectedDate }}">
            <input type="hidden" name="status" value="{{ $selectedStatus ?? '' }}">
        </form>

        <form class="date-control" method="GET" action="{{ route('appointments.index') }}">
            <i class="ti ti-calendar" aria-hidden="true"></i>
            <label for="appt-date-select">Date encoded</label>
            <select id="appt-date-select" name="date" class="js-autosubmit">
                <option value="" {{ empty($selectedDate) ? 'selected' : '' }}>All appointments</option>
                @forelse ($availableDates as $i => $date)
                    <option value="{{ $date }}" {{ $date === $selectedDate ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}{{ $i === 0 ? ' (latest)' : '' }}
                    </option>
                @empty
                @endforelse
            </select>
            <input type="hidden" name="q" value="{{ $search }}">
            <input type="hidden" name="status" value="{{ $selectedStatus ?? '' }}">
            <input type="hidden" name="user" value="{{ $selectedUser ?? '' }}">
        </form>
    </div>
    <div class="action-bar-right">
            <!-- <a href="{{ route('appointments.export') }}" class="btn btn-secondary">
                <i class="ti ti-download" aria-hidden="true"></i> Export CSV
            </a> -->
            <button type="button" class="btn btn-secondary" id="bulk-download-btn" onclick="submitBulkDownload()">
                <i class="ti ti-zip download-icon" aria-hidden="true"></i>
                <i class="ti ti-spinner download-spinner" aria-hidden="true" style="display:none"></i>
                <span class="download-label">Download ZIP</span>
            </button>
            <button type="button" class="btn btn-danger" id="bulk-trash-btn" onclick="openBulkDeleteModal()" disabled>
                <i class="ti ti-trash" aria-hidden="true"></i> Trash selected
            </button>
            <button type="button" class="btn btn-success" id="mark-completed-btn" onclick="openMarkCompletedModal()" disabled>
                <i class="ti ti-check" aria-hidden="true"></i> Mark completed
            </button>
        </div>
    </div>
    <div class="table-card">
        
        <div class="tbl-wrap" style="min-height:160px">
            <table>
                <colgroup>
                    <col style="width:38px"><col style="width:38px"><col style="width:175px">
                    <col style="width:125px"><col style="width:105px"><col style="width:105px">
                    <col style="width:105px"><col style="width:80px"><col style="width:130px">
                </colgroup>
            <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all" aria-label="Select all rows"></th><th>#</th><th>Full name</th><th>District</th>
                    <th>Nature of appt.</th>
                    <th style="white-space:nowrap;position:relative">
                        @php
                            $statusColor = '';
                            if (isset($selectedStatus)) {
                                if ($selectedStatus === 'active') { $statusColor = '#1E90FF'; }
                                elseif ($selectedStatus === 'in_progress') { $statusColor = '#FFB020'; }
                                elseif ($selectedStatus === 'completed') { $statusColor = '#28A745'; }
                            }
                        @endphp
                        <span id="status-header-text" data-status="{{ $selectedStatus ?? '' }}">Status</span>
                        <button id="status-menu-button" type="button" class="status-menu-btn" aria-haspopup="true" aria-expanded="false" title="Filter status">
                            <i class="ti ti-chevron-down" aria-hidden="true"></i>
                        </button>

                        <div id="status-menu" class="status-menu-dropdown">
                            <a class="status-menu-item" href="{{ route('appointments.index', ['q' => $search, 'date' => $selectedDate, 'status' => '', 'nature' => $selectedNature ?? '']) }}">All</a>
                            <a class="status-menu-item status-menu-item--active" href="{{ route('appointments.index', ['q' => $search, 'date' => $selectedDate, 'status' => 'active', 'nature' => $selectedNature ?? '']) }}">Active</a>
                            <a class="status-menu-item status-menu-item--in-progress" href="{{ route('appointments.index', ['q' => $search, 'date' => $selectedDate, 'status' => 'in_progress', 'nature' => $selectedNature ?? '']) }}">In Progress</a>
                        </div>
                    </th>
                     <th>Open</th>
                     <th>Date encoded</th>
                     <th>Encoded By</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($appointments as $i => $a)
                    <tr class="data-row" id="row-{{ $a->id }}">
                        <td><input type="checkbox" class="select-row" value="{{ $a->id }}" aria-label="Select row"></td>
                        <td style="color:var(--text-muted)">{{ $i + 1 }}</td>
                        <td>
                            <div class="name-row" style="display:inline-flex;align-items:center;gap:8px;">
                                <span class="name-text">{{ $a->full_name }}</span>
                            </div>
                            @unless(auth()->user()?->isHr())<div class="tn-code">{{ $a->transaction_number }}</div>@endunless
                        </td>
                        <td>{{ $a->school_district }}</td>
                        <td><span class="badge badge-teal">{{ $a->nature_of_appointment }}</span></td>
                        <td>
                            @php
                                $statusClass = match($a->record_state) {
                                    'deleted' => 'badge-red',
                                    'concluded' => 'badge-green',
                                    'completed' => 'badge-green',
                                    'in_progress' => 'badge-amber',
                                    'active', 'new' => 'badge-blue',
                                    default => 'badge-blue',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}" id="status-badge-{{ $a->id }}">{{ $a->display_record_state }}</span>
                        </td>
                         <td><button type="button" class="btn btn-secondary btn-sm open-btn" onclick="toggleRow({{ $a->id }}, event)" aria-expanded="false" title="Expand details"><i class="ti ti-chevron-down" aria-hidden="true"></i> Expand</button></td>
                         <td style="font-size:12px;color:var(--text-muted)">{{ $a->encoded_at->format('F j, Y g:i A') }}</td>
                         <td style="font-size:12px;color:var(--text-muted)">{{ $a->owner->name ?? '—' }}</td>
                    </tr>
                        <tr class="dropdown-row" id="detail-{{ $a->id }}">
                            <td colspan="9">
                                <div class="drop-panel">
                                    <div class="drop-left">
                                        <span class="drop-label">Downloadables</span>
                                        <a href="{{ route('appointments.exportAfa', $a) }}" class="btn btn-secondary btn-sm doc-download" data-no-loader data-appointment-id="{{ $a->id }}"><i class="ti ti-file-text" style="font-size:12px" aria-hidden="true"></i> Appointment</a>
                                        <a href="{{ route('appointments.downloadChecklist', $a) }}" class="btn btn-secondary btn-sm doc-download" data-no-loader data-appointment-id="{{ $a->id }}"><i class="ti ti-checklist" style="font-size:12px" aria-hidden="true"></i> Checklist</a>
                                        <a href="{{ route('appointments.downloadRai', $a) }}" class="btn btn-secondary btn-sm doc-download" data-no-loader data-appointment-id="{{ $a->id }}"><i class="ti ti-file-text" style="font-size:12px" aria-hidden="true"></i> RAI</a>
                                        <a href="{{ route('appointments.downloadFinalDeliberation', $a) }}" class="btn btn-secondary btn-sm doc-download" data-no-loader data-appointment-id="{{ $a->id }}"><i class="ti ti-file-text" style="font-size:12px" aria-hidden="true"></i> Final Deliberation</a>
                                    </div>
                                    <div class="drop-right">
                                        <button type="button" class="btn btn-blue btn-sm" onclick="openViewSummary({{ $a->id }})"><i class="ti ti-eye icon-sm" aria-hidden="true"></i> View</button>
                                        <button type="button" class="btn btn-success btn-sm" onclick="openEditWizard({{ $a->id }})"><i class="ti ti-edit icon-sm" aria-hidden="true"></i> Edit</button>
                                        <form action="{{ route('appointments.destroy', $a) }}" method="POST" onsubmit="return false;" id="delete-form-{{ $a->id }}">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm" onclick="openDelete({{ $a->id }}, '{{ addslashes($a->full_name) }}')"><i class="ti ti-trash icon-sm" aria-hidden="true"></i> Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                @empty
                    <tr class="no-rows">
                        <td colspan="9" style="border-bottom:0;padding:18px 12px;">
                            <p class="empty-note" style="margin:0;">No appointments found for this date.</p>
                        </td>
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

<div class="overlay" id="overlay-mark-completed">
    <div class="modal" style="max-width:460px">
        <div class="modal-head">
            <span class="modal-title">Mark as Completed</span>
            <button type="button" class="modal-close" onclick="closeModal('overlay-mark-completed')" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="confirm-icon"><i class="ti ti-alert-triangle" style="font-size:44px;color:var(--amber)" aria-hidden="true"></i></div>
            <p class="confirm-msg" style="margin-bottom:12px">Mark the following appointment(s) as completed?</p>

            <div id="mc-appointment-list" style="max-height:200px;overflow-y:auto;margin:8px 0;border:1px solid var(--border);border-radius:6px;padding:8px 12px;background:var(--color-tab-bg);font-size:0.88rem;font-family:var(--font-mono)">
            </div>

            <div id="mc-warning" style="display:none;margin-top:12px">
                <p style="color:var(--red);font-size:0.85rem;margin:8px 0 0">
                    <i class="ti ti-alert-circle" aria-hidden="true"></i>
                    <strong>Action required:</strong> The appointments highlighted in red are still <strong>Active</strong> and need to be moved to <strong>In Progress</strong> first before they can be marked as completed.
                </p>
            </div>

            <div id="mc-help" style="display:none;margin-top:12px">
                <p style="font-size:0.85rem;color:var(--text-muted);margin:8px 0 0">
                    <i class="ti ti-info-circle" aria-hidden="true"></i>
                    All selected appointments are currently <strong>In Progress</strong> and ready to be marked as completed.
                </p>
            </div>

            <p style="margin-top:14px;font-size:0.78rem;color:var(--text-muted)">
                This action will move the selected record(s) to the Archive page. This cannot be undone.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('overlay-mark-completed')">Cancel</button>
            <form id="mark-completed-form" method="POST" action="{{ route('appointments.markCompleted') }}">
                @csrf
                <button type="button" class="btn btn-success" id="mc-confirm-btn" onclick="confirmMarkCompleted()" disabled>
                    <i class="ti ti-check" style="font-size:13px" aria-hidden="true"></i> Yes, mark as completed
                </button>
            </form>
        </div>
    </div>
</div>

@push('modals')
    @include('appointments.partials.wizard-modal')
    @include('appointments.partials.delete-modal')
    @include('appointments.partials.download-modal')
    @include('appointments.partials.view-modal')
    @include('appointments.partials.others-modal')
@endpush

@push('scripts')
<script>
window._pamsAppointmentsUrl = '{{ url('appointments') }}';
window._pamsBulkDestroyUrl  = '{{ route('appointments.bulkDestroy') }}';
window._pamsExportUrl       = '{{ route('appointments.export') }}';
window._pamsLoginUrl        = '{{ route('login') }}';
</script>
<script src="{{ asset('js/appointments-index.js') }}"></script>
@endpush
@endsection