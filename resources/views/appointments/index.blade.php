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
                   onchange="this.form.submit()">
            <select name="nature" onchange="this.form.submit()" aria-label="Filter by appointment nature">
                <option value="">All natures</option>
                <option value="Original" {{ $selectedNature === 'Original' ? 'selected' : '' }}>Original</option>
                <option value="Promotion" {{ $selectedNature === 'Promotion' ? 'selected' : '' }}>Promotion</option>
                <option value="Demotion" {{ $selectedNature === 'Demotion' ? 'selected' : '' }}>Demotion</option>
                <option value="Transfer" {{ $selectedNature === 'Transfer' ? 'selected' : '' }}>Transfer</option>
                <option value="Re-Classification" {{ $selectedNature === 'Re-Classification' ? 'selected' : '' }}>Re-Classification</option>
                <option value="Re-Employment" {{ $selectedNature === 'Re-Employment' ? 'selected' : '' }}>Re-Employment</option>
                <option value="Re-Appointment" {{ $selectedNature === 'Re-Appointment' ? 'selected' : '' }}>Re-Appointment</option>
            </select>
            <input type="hidden" name="date" value="{{ $selectedDate }}">
            <input type="hidden" name="status" value="{{ $selectedStatus ?? '' }}">
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
            <input type="hidden" name="status" value="{{ $selectedStatus ?? '' }}">
            <input type="hidden" name="nature" value="{{ $selectedNature ?? '' }}">
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
            <a href="{{ route('appointments.create') }}" class="btn btn-primary add-entry-btn">
                <i class="ti ti-plus" aria-hidden="true"></i> Add new entry
            </a>
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
                        <span id="status-header-text" style="color:{{ $statusColor ?: 'inherit' }}">Status</span>
                        <button id="status-menu-button" type="button" aria-haspopup="true" aria-expanded="false" title="Filter status" style="background:none;border:0;cursor:pointer;padding:0;margin-left:6px;vertical-align:middle">
                            <i class="ti ti-chevron-down" aria-hidden="true"></i>
                        </button>

                        <div id="status-menu" style="display:none;position:absolute;right:0;top:22px;background:#fff;border:1px solid rgba(0,0,0,0.08);box-shadow:0 6px 12px rgba(0,0,0,0.06);border-radius:4px;z-index:60;min-width:140px;">
                            <a class="status-menu-item" href="{{ route('appointments.index', ['q' => $search, 'date' => $selectedDate, 'status' => '', 'nature' => $selectedNature ?? '']) }}" style="display:block;padding:8px 12px;text-decoration:none;color:inherit">All</a>
                            <a class="status-menu-item" href="{{ route('appointments.index', ['q' => $search, 'date' => $selectedDate, 'status' => 'active', 'nature' => $selectedNature ?? '']) }}" style="display:block;padding:8px 12px;text-decoration:none;color:#1E90FF">Active</a>
                            <a class="status-menu-item" href="{{ route('appointments.index', ['q' => $search, 'date' => $selectedDate, 'status' => 'in_progress', 'nature' => $selectedNature ?? '']) }}" style="display:block;padding:8px 12px;text-decoration:none;color:#FFB020">In Progress</a>
                            <a class="status-menu-item" href="{{ route('appointments.index', ['q' => $search, 'date' => $selectedDate, 'status' => 'completed', 'nature' => $selectedNature ?? '']) }}" style="display:block;padding:8px 12px;text-decoration:none;color:#28A745">Completed</a>
                        </div>
                    </th>
                    <th>Original appt.</th>
                    <th>Open</th>
                    <th>Date encoded</th>
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
                        <td>{{ optional($a->date_original_appointment)->format('Y-m-d') ?? '—' }}</td>
                        <td><button type="button" class="btn btn-secondary btn-sm open-btn" onclick="toggleRow({{ $a->id }}, event)" aria-expanded="false" title="Expand details"><i class="ti ti-chevron-down" aria-hidden="true"></i> Expand</button></td>
                        <td style="font-size:12px;color:var(--text-muted)">{{ $a->encoded_at->format('F j, Y g:i A') }}</td>
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
                                        <button type="button" class="btn btn-blue btn-sm" onclick="openViewSummary({{ $a->id }})"><i class="ti ti-eye" style="font-size:12px" aria-hidden="true"></i> View</button>
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

@if(session('tn_saved'))
    <div class="overlay show" id="tn-saved-overlay" style="z-index:300">
        <div class="modal" style="max-width:420px">
            <div class="modal-body" style="text-align:center;padding:30px 28px">
                <div style="font-size:2.6rem;color:var(--green)"><i class="ti ti-circle-check" aria-hidden="true"></i></div>
                <h5 style="font-weight:800;margin:10px 0 2px;color:var(--text-primary)">Transaction number saved</h5>
                <p style="font-size:.85rem;color:var(--text-muted);margin:0 0 14px">{{ session('tn_name') }}</p>
                <div style="background:var(--accent-light);border:1px solid var(--border);border-radius:10px;padding:12px;font-size:1.05rem;font-weight:700;letter-spacing:.04em;color:var(--text-primary)">{{ session('tn_saved') }}</div>
                <p style="font-size:.78rem;color:var(--text-muted);margin:12px 0 0">Please double-check the number above for any typos.</p>
                <button type="button" class="btn btn-primary" style="margin-top:16px;width:100%" onclick="document.getElementById('tn-saved-overlay').classList.remove('show')">Done</button>
            </div>
        </div>
    </div>
@endif

@push('modals')
    @include('appointments.partials.wizard-modal')
    @include('appointments.partials.delete-modal')
    @include('appointments.partials.download-modal')
    @include('appointments.partials.view-modal')
@endpush

@push('scripts')
<script>
function toggleRow(id, e) {
    e.stopPropagation();
    const row = document.getElementById('detail-' + id);
    const btn = document.querySelector('#row-' + id + ' .open-btn');
    const isOpen = row.classList.contains('open');

    document.querySelectorAll('.dropdown-row.open').forEach(r => r.classList.remove('open'));
    document.querySelectorAll('.open-btn.open').forEach(b => { b.classList.remove('open'); b.setAttribute('aria-expanded', 'false'); });

    if (!isOpen) {
        row.classList.add('open');
        btn.classList.add('open');
        btn.setAttribute('aria-expanded', 'true');
    }
}

if (!window.__hrRowClose) {
    window.__hrRowClose = true;
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.open-btn') && !e.target.closest('.dropdown-row')) {
            document.querySelectorAll('.dropdown-row.open').forEach(r => r.classList.remove('open'));
            document.querySelectorAll('.open-btn.open').forEach(b => { b.classList.remove('open'); b.setAttribute('aria-expanded', 'false'); });
        }
    });
}

function openDelete(id, name) {
    document.getElementById('del-name').textContent = name;
    document.getElementById('overlay-del').classList.add('show');
    document.getElementById('overlay-del').dataset.formId = 'delete-form-' + id;
}

function openEditWizard(id) {
    const form   = document.getElementById('wizard-form');
    const title  = document.getElementById('wizard-modal-title');
    const method = document.getElementById('wizard-method');

    if (!form || !title || !method) {
        alert('Edit form is not available. Please refresh the page and try again.');
        return;
    }

    form.reset();
    form.action = '{{ url("appointments") }}/' + id;
    method.value = 'PUT';
    title.textContent = 'Loading…';

    fetch('{{ url("appointments") }}/' + id, {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin'
    })
        .then(response => {
            if (response.status === 403) {
                throw new Error('You do not have permission to edit this appointment.');
            }
            if (response.status === 401) {
                window.location.href = '{{ route("login") }}';
                throw new Error('Session expired. Redirecting to login…');
            }
            if (!response.ok) {
                throw new Error('Server error (' + response.status + '). Please try again.');
            }
            return response.json();
        })
        .then(data => {
            populateWizardForm(data);
            wzCurrent = 0;
            wzUpdateUI();
            // Re-query at callback time in case the DOM was swapped mid-flight.
            const t = document.getElementById('wizard-modal-title');
            if (t) t.textContent = 'Edit appointment';
            const overlay = document.getElementById('overlay-wizard');
            if (overlay) overlay.classList.add('show');
        })
        .catch(err => {
            const t = document.getElementById('wizard-modal-title');
            if (t) t.textContent = 'Edit appointment';
            alert(err.message || 'Unable to load appointment details for editing.');
        });
}


function populateWizardForm(data) {
    const setValue = (id, value) => {
        const el = document.getElementById(id);
        if (!el) return;
        if (el.type === 'date') {
            const m = String(value ?? '').match(/^(\d{4}-\d{2}-\d{2})/);
            el.value = m ? m[1] : '';
        } else {
            el.value = value ?? '';
        }
    };

    setValue('f-last', data.last_name);
    setValue('f-first', data.first_name);
    setValue('f-middle', data.middle_name);
    setValue('f-ext', data.extension_name);
    setValue('f-sex', data.sex);
    setValue('f-dob', data.date_of_birth);
    setValue('f-tin', data.tin);
    setValue('f-pwd', data.pwd);
    setValue('f-ip', data.ip_group_member);
    setValue('f-ethnicity', data.ethnicity);

    setValue('f-pos', data.position_title);
    setValue('f-pfrom', data.position_from);
    setValue('f-pto', data.position_to);
    setValue('f-sg', data.salary_grade);
    setValue('f-step', data.salary_grade_step);
    setValue('f-sal', data.monthly_salary);
    setValue('f-estatus', data.employee_status);
    setValue('f-salwords', data.compensation_words);
    setValue('f-salnums', data.compensation_numbers);
    setValue('f-nature', data.nature_of_appointment);
    setValue('f-reason', data.reason);
    setValue('f-poslevel', data.position_level);
    setValue('f-aptstatus', data.appointment_status);

    setValue('f-dept', data.department);
    setValue('f-school', data.school_district);
    setValue('f-school-new', data.school);
    setValue('f-sector', data.sector);
    setValue('f-agency', data.agency_name);
    setValue('f-plantilla-item', data.plantilla_item_number);
    setValue('f-plantilla-page', data.plantilla_page_number);
    setValue('f-odc', data.odc_number);
    setValue('f-drec', data.date_received_records);
    setValue('f-dhr', data.date_received_hr);
    setValue('f-prev', data.previous_incumbent);
    setValue('f-incumbent', data.incumbent);
    setValue('f-pubmode', data.publication_mode);

    setValue('f-elig', data.eligibility_type);
    setValue('f-eligvalid', data.eligibility_validity);
    setValue('f-eligfirst', data.eligibility_first_used);
    setValue('f-dlp', data.date_last_promotion);

    // New template fields
    setValue('f-natural', data.natural_vacancy);
    setValue('f-dosign', data.date_of_signing);
    setValue('f-pubdate-from', data.publication_date_from);
    setValue('f-pubdate-to', data.publication_date_to);
    setValue('rx-rai-sub-from', data.substitute_from);
    setValue('rx-rai-sub-to', data.substitute_to);
    setValue('f-assessment', data.assessment_date);
    setValue('f-deliberation', data.deliberation_date);
    setValue('f-education', data.education);
    setValue('f-shs', data.senior_high_school);
    setValue('f-strand', data.senior_high_strand);
    setValue('f-teaching-level', data.teaching_level);
    setValue('f-nonteaching', data.non_teaching);

    if (typeof syncReadonly === 'function') syncReadonly();
    if (typeof syncPwdType === 'function') syncPwdType();
    if (typeof syncChecklist === 'function') syncChecklist();
    if (typeof syncFinalDeliberation === 'function') syncFinalDeliberation();
    if (typeof syncDateFieldsByStatus === 'function') syncDateFieldsByStatus();
}

function openViewSummary(id) {
    const modal = document.getElementById('overlay-view');
    const content = document.getElementById('view-summary-content');

    modal.classList.add('show');
    content.innerHTML = '<p class="loading">Loading appointment summary…</p>';

    fetch('{{ url("appointments") }}/' + id, {
        headers: { 'Accept': 'application/json' }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to load appointment details.');
            }
            return response.json();
        })
        .then(data => {
            content.innerHTML = buildAppointmentSummary(data);
            initViewTabs();
        })
        .catch(() => {
            content.innerHTML = '<p class="text-danger">Unable to load summary. Please try again.</p>';
        });
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    }[char]));
}

function initViewTabs() {
    const triggers = document.querySelectorAll('.view-tab-trigger');
    if (!triggers.length) return;

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const target = trigger.dataset.tab;
            document.querySelectorAll('.view-tab-trigger').forEach((btn) => {
                const isActive = btn === trigger;
                btn.classList.toggle('active', isActive);
                btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
            document.querySelectorAll('.view-tab-panel').forEach((panel) => {
                const isActive = panel.dataset.tab === target;
                panel.classList.toggle('active', isActive);
                panel.hidden = !isActive;
            });
        });
    });
}

function buildAppointmentSummary(data) {
    const employeeName = `${data.last_name || ''}, ${data.first_name || ''}${data.middle_name ? ' ' + data.middle_name : ''}${data.extension_name ? ' ' + data.extension_name : ''}`.replace(/,\s+/g, ', ').trim();

    const appointmentRows = [
        ['Full name', employeeName],
        ['Position', data.position_title],
        ['Salary grade', data.salary_grade],
        ['Step', data.salary_grade_step],
        ['Employee status', data.employee_status],
        ['District', data.school_district],
        ['School', data.school],
        ['Plantilla number', data.plantilla_item_number],
        ['Page number', data.plantilla_page_number],
        ['Salary in words', data.compensation_words],
        ['Salary in numbers', data.compensation_numbers],
        ['Appointment nature', data.nature_of_appointment],
        ['Incumbent', data.incumbent || 'Vacant'],
        ['Reason of Incumbent', data.natural_vacancy || 'N/A'],
        ['Date of signing', formatDate(data.date_of_signing)],
        ['Publication Date (FROM)', data.employee_status === 'Permanent' ? formatDate(data.publication_date_from) : 'N/A'],
        ['Publication Date (TO)', data.employee_status === 'Permanent' ? formatDate(data.publication_date_to) : 'N/A'],
        ['Assessment Date', data.employee_status === 'Permanent' ? formatDate(data.assessment_date) : 'N/A'],
        ['Deliberation Date', data.employee_status === 'Permanent' ? formatDate(data.deliberation_date) : 'N/A'],
    ];

    const checklistRows = [
        ['Employee name', employeeName],
        ['Position', data.position_title],
        ['Salary grade', data.salary_grade],
        ['Salary number', data.compensation_numbers],
        ['Date of signing', formatDate(data.date_of_signing)],
        ['Senior high school?', data.senior_high_school || 'N/A'],
        ['Strand', data.senior_high_school === 'Yes' ? (data.senior_high_strand || 'N/A') : 'N/A'],
        ['Teaching Level', data.senior_high_school === 'No' ? (data.teaching_level || 'N/A') : 'N/A'],
        ['Eligibility', data.eligibility_type || 'N/A'],
        ['Date of Validity', formatDate(data.eligibility_validity)],
        ['First time used?', data.eligibility_first_used || 'N/A'],
    ];

    const raiRows = [
        ['Employee name', employeeName],
        ['Position', data.position_title],
        ['Plantilla number', data.plantilla_item_number],
        ['Salary grade', data.salary_grade],
        ['Salary number', data.compensation_numbers],
        ['Employment status', data.employee_status],
        ['Nature of Appointment', data.nature_of_appointment],
        ['Substitute FROM', data.employee_status === 'Substitute' || data.employee_status === 'Provisional' ? formatDate(data.substitute_from) : 'N/A'],
        ['Substitute TO', data.employee_status === 'Substitute' || data.employee_status === 'Provisional' ? formatDate(data.substitute_to) : 'N/A'],
    ];

    const finalDeliberationRows = [
        ['Employee name', employeeName],
        ['Position', data.position_title],
        ['Date of signing', formatDate(data.date_of_signing)],
    ];

    const monitoringRows = [
        ['Date of Last Promotion', formatDate(data.date_last_promotion)],
        ['Position From', data.position_from],
        ['Name of Previous Incumbent', data.previous_incumbent || 'Vacant'],
        ['Position Level', data.position_level],
        ['Sex', data.sex],
        ['Date of Birth', formatDate(data.date_of_birth)],
        ['PWD?', data.pwd],
        ['Type of Disability', data.type_of_disability || (data.pwd === 'No' ? 'N/A' : '—')],
        ['Member of IP Group?', data.ip_group_member],
        ['Ethnicity', data.ethnicity || '—'],
    ];

    const sections = [
        { key: 'appointment', title: 'Appointment', rows: appointmentRows },
        { key: 'checklist', title: 'Checklist', rows: checklistRows },
        { key: 'rai', title: 'RAI', rows: raiRows },
        { key: 'final', title: 'Final Deliberation', rows: finalDeliberationRows },
        { key: 'monitoring', title: 'Monitoring Data', rows: monitoringRows },
    ];

    return `
        <div class="view-summary-shell">
            <div class="view-summary-tabs" role="tablist" aria-label="Appointment summary sections">
                ${sections.map((section) => `
                    <button type="button" class="view-tab-trigger ${section.key === 'appointment' ? 'active' : ''}" role="tab" aria-selected="${section.key === 'appointment' ? 'true' : 'false'}" data-tab="${section.key}">${section.title}</button>
                `).join('')}
            </div>
            <div class="view-tab-panels">
                ${sections.map((section) => `
                    <div class="view-tab-panel ${section.key === 'appointment' ? 'active' : ''}" data-tab="${section.key}" role="tabpanel" ${section.key === 'appointment' ? '' : 'hidden'}>
                        <div class="view-summary-section">
                            <h3>${escapeHtml(section.title)}</h3>
                            <div class="review-grid">
                                ${section.rows.map((row) => `
                                    <div class="review-row"><span class="review-key">${escapeHtml(row[0])}</span><span class="review-val">${escapeHtml(row[1] || '—')}</span></div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

function formatDate(value) {
    if (!value) return '—';
    return value.slice(0, 10);
}

function formatDateTime(value) {
    if (!value) return '—';
    return value.replace('T', ' ').slice(0, 16);
}

function statusBadgeClass(recordState) {
    const classes = {
        deleted: 'badge-red',
        concluded: 'badge-green',
        completed: 'badge-green',
        in_progress: 'badge-amber',
        active: 'badge-blue',
        new: 'badge-blue',
    };

    return classes[recordState] || 'badge-blue';
}

function parseDownloadFilename(contentDisposition) {
    if (!contentDisposition) {
        return 'download';
    }

    const match = contentDisposition.match(/filename\*?=(?:UTF-8''|"?)([^";]+)/i);
    if (!match) {
        return 'download';
    }

    try {
        return decodeURIComponent(match[1].replace(/['"]/g, ''));
    } catch (error) {
        return match[1].replace(/['"]/g, '');
    }
}

function triggerBrowserDownload(blob, filename) {
    const objectUrl = URL.createObjectURL(blob);
    const anchor = document.createElement('a');
    anchor.href = objectUrl;
    anchor.download = filename;
    document.body.appendChild(anchor);
    anchor.click();
    anchor.remove();
    URL.revokeObjectURL(objectUrl);
}

function refreshAppointmentStatus(appointmentId) {
    return fetch('{{ url("appointments") }}/' + appointmentId, {
        headers: { 'Accept': 'application/json' }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to refresh appointment status.');
            }

            return response.json();
        })
        .then(data => {
            const badge = document.getElementById('status-badge-' + appointmentId);
            if (!badge) {
                return;
            }

            badge.textContent = data.display_record_state || '';
            badge.className = 'badge ' + statusBadgeClass(data.record_state);
        });
}

async function handleDocumentDownload(event, link) {
    event.preventDefault();

    if (link.dataset.downloading === '1') {
        return;
    }

    const appointmentId = link.dataset.appointmentId;
    const originalHtml = link.innerHTML;

    link.dataset.downloading = '1';
    link.setAttribute('aria-busy', 'true');
    link.classList.add('is-downloading');
    link.innerHTML = '<i class="ti ti-loader ti-spin" style="font-size:12px" aria-hidden="true"></i>';

    const clearLoading = () => {
        delete link.dataset.downloading;
        link.removeAttribute('aria-busy');
        link.classList.remove('is-downloading');
        link.innerHTML = originalHtml;
    };

    try {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 60000);

        const response = await fetch(link.href, { signal: controller.signal });
        clearTimeout(timeout);

        if (!response.ok) {
            throw new Error('Download failed.');
        }

        const blob = await response.blob();
        const filename = parseDownloadFilename(response.headers.get('Content-Disposition'));
        triggerBrowserDownload(blob, filename);
    } catch (error) {
        if (error.name !== 'AbortError') {
            alert('Unable to download document. Please try again.');
        }
    } finally {
        clearLoading();
    }

    // Refresh the status badge in the background so it can never block
    // clearing the download spinner.
    refreshAppointmentStatus(appointmentId).catch(() => {});
}

async function downloadResponseAsFile(response, fallbackName, onComplete) {
    const blob = await response.blob();
    const filename = parseDownloadFilename(response.headers.get('Content-Disposition')) || fallbackName;
    triggerBrowserDownload(blob, filename);
    if (typeof onComplete === 'function') {
        onComplete();
    }
}

function openBulkDeleteModal() {
    const selected = getSelectedIds();
    if (!selected.length) {
        alert('Please select at least one appointment first.');
        return;
    }

    document.getElementById('del-name').textContent = selected.length === 1
        ? 'the selected appointment'
        : `${selected.length} selected appointments`;
    document.getElementById('overlay-del').classList.add('show');
    document.getElementById('overlay-del').dataset.bulkIds = JSON.stringify(selected);
    document.getElementById('overlay-del').dataset.formId = '';
}

function confirmDelete() {
    const bulkIds = document.getElementById('overlay-del').dataset.bulkIds;
    if (bulkIds) {
        submitBulkDestroy(JSON.parse(bulkIds));
        return;
    }

    const formId = document.getElementById('overlay-del').dataset.formId;
    document.getElementById(formId).submit();
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.select-row:checked')).map(el => el.value);
}

function submitBulkDestroy(ids) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('appointments.bulkDestroy') }}';

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = token;
    form.appendChild(csrfInput);

    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);

    ids.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}

var pendingDownloadIds = [];

function submitBulkDownload() {
    const ids = getSelectedIds();
    if (!ids.length) {
        showDownloadModal('Please select at least one appointment to download.');
        return;
    }

    pendingDownloadIds = ids;
    document.getElementById('download-count').textContent = ids.length;

    const list = document.getElementById('download-confirm-list');
    if (list) {
        list.innerHTML = ids.map(id => {
            const row = document.getElementById('row-' + id);
            const nameEl = row ? row.querySelector('.name-text') : null;
            const name = nameEl ? nameEl.textContent.trim() : ('Record #' + id);
            return '<li><span class="pc-name">' + escapeHtml(name) + '</span></li>';
        }).join('');
    }

    document.getElementById('overlay-download-confirm').classList.add('show');
}

function confirmBulkDownload() {
    const ids = pendingDownloadIds;
    if (!ids.length) return;

    closeModal('overlay-download-confirm');

    const button = document.getElementById('bulk-download-btn');
    const downloadIcon = button ? button.querySelector('.download-icon') : null;
    const downloadSpinner = button ? button.querySelector('.download-spinner') : null;
    const downloadLabel = button ? button.querySelector('.download-label') : null;
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const formData = new FormData();
    formData.append('_token', token);
    ids.forEach(id => formData.append('ids[]', id));

    if (button) {
        button.disabled = true;
        button.classList.add('is-downloading');
        button.setAttribute('aria-busy', 'true');
        if (downloadIcon) downloadIcon.style.display = 'none';
        if (downloadSpinner) downloadSpinner.style.display = 'inline-block';
        if (downloadLabel) downloadLabel.textContent = 'Downloading...';
    }

    fetch('{{ route('appointments.export') }}', {
        method: 'POST',
        body: formData,
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Bulk download failed.');
            }

            return downloadResponseAsFile(response, 'appointments.zip', () => {
                if (button) {
                    button.disabled = false;
                    button.classList.remove('is-downloading');
                    button.removeAttribute('aria-busy');
                    if (downloadIcon) downloadIcon.style.display = 'inline-block';
                    if (downloadSpinner) downloadSpinner.style.display = 'none';
                    if (downloadLabel) {
                        const selected = getSelectedIds();
                        downloadLabel.textContent = selected.length > 0
                            ? `Download ZIP (${selected.length})`
                            : 'Download ZIP';
                    }
                }
            });
        })
        .then(() => {
            ids.forEach(id => refreshAppointmentStatus(id).catch(() => {}));
        })
        .catch(() => {
            showDownloadModal('Unable to download selected appointments. Please try again.');
        })
        .finally(() => {
            pendingDownloadIds = [];
            if (button) {
                button.disabled = false;
                button.classList.remove('is-downloading');
                button.removeAttribute('aria-busy');
                if (downloadIcon) downloadIcon.style.display = 'inline-block';
                if (downloadSpinner) downloadSpinner.style.display = 'none';
                if (downloadLabel) {
                    const selected = getSelectedIds();
                    downloadLabel.textContent = selected.length > 0
                        ? `Download ZIP (${selected.length})`
                        : 'Download ZIP';
                }
            }
        });
}

var toastTimer = null;
function showToast(message) {
    let toast = document.getElementById('app-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'app-toast';
        toast.className = 'app-toast';
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        document.body.appendChild(toast);
    }

    toast.textContent = message;
    toast.classList.add('show');

    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 3500);
}

function closeModal(id) { document.getElementById(id).classList.remove('show'); }

function showDownloadModal(msg, title) {
    if (title) document.getElementById('download-modal-title').textContent = title;
    else document.getElementById('download-modal-title').textContent = 'Download not available';
    document.getElementById('download-modal-msg').textContent = msg;
    document.getElementById('overlay-download').classList.add('show');
}

// Use event delegation for overlay backdrop-click-to-close so it works
// regardless of when modal elements are added to the DOM.
// Deduplicate: only register once across AJAX navigations.
if (!window.__hrOverlayClose) {
    window.__hrOverlayClose = true;
    document.addEventListener('click', function (e) {
        if (e.target.classList && e.target.classList.contains('overlay') && e.target.id) {
            const el = document.getElementById(e.target.id);
            if (el) el.classList.remove('show');
        }
    });
}

function initIndexPage() {
    // PAGE GUARD: only run when the appointments data table is in main content.
    // Prevents this handler from firing on non-index pages when it persists
    // as an hr:page:load listener across AJAX navigations.
    const mainEl = document.querySelector('main.content') || document.querySelector('main');
    if (!mainEl || !mainEl.querySelector('#bulk-trash-btn')) return;

    const selectAll = document.getElementById('select-all');
    const rows = document.querySelectorAll('.select-row');
    const bulkTrashBtn = document.getElementById('bulk-trash-btn');
    const bulkDownloadBtn = document.getElementById('bulk-download-btn');

    const updateBulkButton = () => {
        const selected = getSelectedIds();
        const hasSelection = selected.length > 0;

        if (bulkTrashBtn) {
            bulkTrashBtn.disabled = !hasSelection;
        }
        if (bulkDownloadBtn) {
            const label = bulkDownloadBtn.querySelector('.download-label');
            if (label) {
                label.textContent = hasSelection
                    ? `Download ZIP (${selected.length})`
                    : 'Download ZIP';
            }
        }

        if (selectAll) {
            const total = rows.length;
            selectAll.checked = hasSelection && selected.length === total;
            selectAll.indeterminate = hasSelection && selected.length < total;
        }
    };

    const syncRowHighlight = () => {
        rows.forEach(row => {
            const tr = row.closest('tr.data-row');
            if (!tr) return;
            tr.classList.toggle('selected-row', row.checked);
        });
    };

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            rows.forEach(row => { row.checked = this.checked; });
            syncRowHighlight();
            updateBulkButton();
        });
    }

    rows.forEach(row => {
        row.addEventListener('change', function () {
            if (!this.checked && selectAll) {
                selectAll.checked = false;
            }
            syncRowHighlight();
            updateBulkButton();
        });
    });

    syncRowHighlight();
    updateBulkButton();

    document.querySelectorAll('.doc-download').forEach(link => {
        link.addEventListener('click', function (event) {
            handleDocumentDownload(event, this);
        });
    });

    // Status menu toggle inside the table header
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('#status-menu-button');
        if (btn) {
            e.preventDefault();
            e.stopPropagation();
            const menu = document.getElementById('status-menu');
            if (!menu) return;
            const isOpen = menu.style.display === 'block';
            document.querySelectorAll('#status-menu').forEach(m => m.style.display = 'none');
            menu.style.display = isOpen ? 'none' : 'block';
            return;
        }

        const menu = e.target.closest('#status-menu');
        if (!menu) {
            document.querySelectorAll('#status-menu').forEach(m => m.style.display = 'none');
        }
    });
}

// Run on full page load and on every AJAX navigation.
// Deduplicate: remove any previously registered handler before adding a fresh one.
if (window.__hrIndexPageLoad) {
    document.removeEventListener('hr:page:load', window.__hrIndexPageLoad);
}
window.__hrIndexPageLoad = initIndexPage;

document.addEventListener('DOMContentLoaded', initIndexPage);
document.addEventListener('hr:page:load', initIndexPage);
if (document.readyState !== 'loading') {
    initIndexPage();
}
</script>
@endpush
@endsection