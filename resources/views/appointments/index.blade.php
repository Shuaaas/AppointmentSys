@extends('layout.app')

@section('title', 'Appointment Data Entry')

@section('content')
<div class="action-bar">
    <form class="search-wrap" method="GET" action="{{ route('appointments.index') }}">
        <i class="ti ti-search" aria-hidden="true"></i>
        <input type="text" name="q" value="{{ $search }}" placeholder="Search by name, school, eligibility…"
               onchange="this.form.submit()">
        <select name="nature" onchange="this.form.submit()" aria-label="Filter by appointment nature">
            <option value="">All natures</option>
            <option value="Original" {{ $selectedNature === 'Original' ? 'selected' : '' }}>Original</option>
            <option value="Promotion" {{ $selectedNature === 'Promotion' ? 'selected' : '' }}>Promotion</option>
            <option value="Transfer" {{ $selectedNature === 'Transfer' ? 'selected' : '' }}>Transfer</option>
            <option value="Reappointment" {{ $selectedNature === 'Reappointment' ? 'selected' : '' }}>Reappointment</option>
            <option value="Reinstatement" {{ $selectedNature === 'Reinstatement' ? 'selected' : '' }}>Reinstatement</option>
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

    <div class="action-bar-right">
        <!-- <a href="{{ route('appointments.export') }}" class="btn btn-secondary">
            <i class="ti ti-download" aria-hidden="true"></i> Export CSV
        </a> -->
        <button type="button" class="btn btn-secondary" id="bulk-download-btn" onclick="submitBulkDownload()">
            <i class="ti ti-zip" aria-hidden="true"></i> Download Selected (ZIP)
        </button>
        <button type="button" class="btn btn-danger" id="bulk-trash-btn" onclick="openBulkDeleteModal()" disabled>
            <i class="ti ti-trash" aria-hidden="true"></i> Trash selected
        </button>
        <button type="button" class="btn btn-primary" onclick="openWizard()">
            <i class="ti ti-plus" aria-hidden="true"></i> Add new entry
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
                    <th><input type="checkbox" id="select-all" aria-label="Select all rows"></th><th>#</th><th>Full name</th><th>School / district</th>
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
                    <th>Eligibility</th><th>Date encoded</th>
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
                                <button type="button" class="name-btn" onclick="toggleRow({{ $a->id }}, event)" aria-expanded="false" title="Toggle details">
                                    <i class="ti ti-chevron-down" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="tn-code">{{ $a->transaction_number }}</div>
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
                        <td><span class="badge badge-teal">{{ $a->eligibility_type ?? '—' }}</span></td>
                        <td style="font-size:12px;color:var(--text-muted)">{{ $a->encoded_at->format('F j, Y g:i A') }}</td>
                    </tr>
                    <tr class="dropdown-row" id="detail-{{ $a->id }}">
                        <td colspan="9">
                            <div class="drop-panel">
                                <div class="drop-left">
                                    <span class="drop-label">Downloadables</span>
                                    <a href="{{ route('appointments.exportAfa', $a) }}" class="btn btn-secondary btn-sm doc-download" data-appointment-id="{{ $a->id }}"><i class="ti ti-file-text" style="font-size:12px" aria-hidden="true"></i> Appointment</a>
                                    <a href="{{ route('appointments.downloadChecklist', $a) }}" class="btn btn-secondary btn-sm doc-download" data-appointment-id="{{ $a->id }}"><i class="ti ti-checklist" style="font-size:12px" aria-hidden="true"></i> Checklist</a>
                                    <a href="{{ route('appointments.downloadRai', $a) }}" class="btn btn-secondary btn-sm doc-download" data-appointment-id="{{ $a->id }}"><i class="ti ti-file-text" style="font-size:12px" aria-hidden="true"></i> RAI</a>
                                    <a href="{{ route('appointments.downloadFinalDeliberation', $a) }}" class="btn btn-secondary btn-sm doc-download" data-appointment-id="{{ $a->id }}"><i class="ti ti-file-text" style="font-size:12px" aria-hidden="true"></i> Final Deliberation</a>
                                </div>
                                <div class="drop-right">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openViewSummary({{ $a->id }})"><i class="ti ti-eye" style="font-size:12px" aria-hidden="true"></i> View</button>
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

@push('modals')
    @include('appointments.partials.wizard-modal')
    @include('appointments.partials.delete-modal')
    @include('appointments.partials.view-modal')
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

function openEditWizard(id) {
    const form = document.getElementById('wizard-form');
    const title = document.getElementById('wizard-modal-title');
    const method = document.getElementById('wizard-method');

    form.reset();
    form.action = '{{ url("appointments") }}/' + id;
    method.value = 'PUT';
    title.textContent = 'Edit appointment';

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
            populateWizardForm(data);
            wzCurrent = 0;
            wzUpdateUI();
            document.getElementById('overlay-wizard').classList.add('show');
        })
        .catch(() => {
            alert('Unable to load appointment details for editing.');
        });
}

function populateWizardForm(data) {
    const setValue = (id, value) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.value = value ?? '';
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
    setValue('f-doa', data.date_original_appointment);
    setValue('f-dlp', data.date_last_promotion);
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
        })
        .catch(() => {
            content.innerHTML = '<p class="text-danger">Unable to load summary. Please try again.</p>';
        });
}

function buildAppointmentSummary(data) {
    const sections = [
        {
            title: 'Personal information',
            rows: [
                ['Full name', `${data.last_name}, ${data.first_name}${data.middle_name ? ' ' + data.middle_name : ''}${data.extension_name ? ' ' + data.extension_name : ''}`],
                ['Sex', data.sex],
                ['Date of birth', formatDate(data.date_of_birth)],
                ['TIN', data.tin],
                ['PWD?', data.pwd],
                ['IP group member?', data.ip_group_member],
                ['Ethnicity', data.ethnicity],
            ],
        },
        {
            title: 'Position & salary',
            rows: [
                ['Title', data.position_title],
                ['Position from', formatDate(data.position_from)],
                ['Position to', formatDate(data.position_to)],
                ['Salary grade', data.salary_grade],
                ['Salary step', data.salary_grade_step],
                ['Monthly salary', data.monthly_salary],
                ['Employee status', data.employee_status],
                ['Appointment nature', data.nature_of_appointment],
                ['Position level', data.position_level],
                ['Appointment status', data.appointment_status],
            ],
        },
        {
            title: 'Agency information',
            rows: [
                ['Department', data.department],
                ['School / district', data.school_district],
                ['Sector', data.sector],
                ['Agency name', data.agency_name],
                ['Plantilla item no.', data.plantilla_item_number],
                ['Plantilla page no.', data.plantilla_page_number],
                ['ODC number', data.odc_number],
                ['Date received (records)', formatDate(data.date_received_records)],
                ['Date received (HR)', formatDate(data.date_received_hr)],
                ['Previous incumbent', data.previous_incumbent],
                ['Incumbent', data.incumbent],
                ['Publication mode', data.publication_mode],
            ],
        },
        {
            title: 'Eligibility & history',
            rows: [
                ['Eligibility type', data.eligibility_type],
                ['Eligibility validity', formatDate(data.eligibility_validity)],
                ['First time used', data.eligibility_first_used],
                ['Original appointment date', formatDate(data.date_original_appointment)],
                ['Last promotion date', formatDate(data.date_last_promotion)],
                ['Record state', data.display_record_state ?? ''],
                ['Date concluded', formatDate(data.date_concluded)],
                ['Encoding personnel', data.encoding_personnel],
                ['Date encoded', formatDateTime(data.encoded_at)],
            ],
        },
    ];

    return sections.map(section => `
        <div class="review-section">
            <h3>${section.title}</h3>
            <div class="review-grid">
                ${section.rows.map(row => `
                    <div class="review-row"><span class="review-key">${row[0]}</span><span class="review-val">${row[1] || '—'}</span></div>
                `).join('')}
            </div>
        </div>
    `).join('');
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
    link.dataset.downloading = '1';
    link.setAttribute('aria-busy', 'true');
    link.classList.add('is-downloading');

    try {
        const response = await fetch(link.href);
        if (!response.ok) {
            throw new Error('Download failed.');
        }

        const blob = await response.blob();
        const filename = parseDownloadFilename(response.headers.get('Content-Disposition'));
        triggerBrowserDownload(blob, filename);
        await refreshAppointmentStatus(appointmentId);
    } catch (error) {
        alert('Unable to download document. Please try again.');
    } finally {
        delete link.dataset.downloading;
        link.removeAttribute('aria-busy');
        link.classList.remove('is-downloading');
    }
}

async function downloadResponseAsFile(response, fallbackName) {
    const blob = await response.blob();
    const filename = parseDownloadFilename(response.headers.get('Content-Disposition')) || fallbackName;
    triggerBrowserDownload(blob, filename);
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

function submitBulkDownload() {
    const ids = getSelectedIds();
    if (!ids.length) {
        alert('Please select at least one appointment to download.');
        return;
    }

    const button = document.getElementById('bulk-download-btn');
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const formData = new FormData();
    formData.append('_token', token);
    ids.forEach(id => formData.append('ids[]', id));

    if (button) {
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
    }

    fetch('{{ route('appointments.export') }}', {
        method: 'POST',
        body: formData,
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Bulk download failed.');
            }

            return downloadResponseAsFile(response, 'appointments.zip');
        })
        .then(() => Promise.all(ids.map(id => refreshAppointmentStatus(id))))
        .catch(() => {
            alert('Unable to download selected appointments. Please try again.');
        })
        .finally(() => {
            if (button) {
                button.disabled = false;
                button.removeAttribute('aria-busy');
            }
        });
}

function closeModal(id) { document.getElementById(id).classList.remove('show'); }

document.querySelectorAll('.overlay').forEach(ov => {
    ov.addEventListener('click', function (e) { if (e.target === ov) closeModal(ov.id); });
});

document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all');
    const rows = document.querySelectorAll('.select-row');
    const bulkTrashBtn = document.getElementById('bulk-trash-btn');

    const updateBulkButton = () => {
        const selected = getSelectedIds();
        bulkTrashBtn.disabled = selected.length === 0;
    };

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            rows.forEach(row => { row.checked = this.checked; });
            updateBulkButton();
        });
    }

    rows.forEach(row => {
        row.addEventListener('change', function () {
            if (!this.checked && selectAll) {
                selectAll.checked = false;
            }
            updateBulkButton();
        });
    });

    document.querySelectorAll('.doc-download').forEach(link => {
        link.addEventListener('click', function (event) {
            handleDocumentDownload(event, this);
        });
    });

    // Status menu toggle inside the table header
    const statusBtn = document.getElementById('status-menu-button');
    const statusMenu = document.getElementById('status-menu');
    if (statusBtn && statusMenu) {
        statusBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = statusMenu.style.display === 'block';
            // close other menus
            document.querySelectorAll('#status-menu').forEach(m => m.style.display = 'none');
            statusMenu.style.display = isOpen ? 'none' : 'block';
        });

        document.addEventListener('click', function (ev) {
            if (!ev.target.closest('#status-menu') && !ev.target.closest('#status-menu-button')) {
                statusMenu.style.display = 'none';
            }
        });
    }
});
</script>
@endpush
@endsection