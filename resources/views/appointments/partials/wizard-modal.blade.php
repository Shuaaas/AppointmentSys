<div class="overlay" id="overlay-wizard">
    <div class="modal wizard-modal">
        <div class="modal-head">
            <span class="modal-title" id="wizard-modal-title">Add new appointment</span>
            <button type="button" class="modal-close" onclick="closeModal('overlay-wizard')" aria-label="Close">&times;</button>
        </div>

        <form id="wizard-form" method="POST" action="{{ route('appointments.store') }}">
            @csrf
            <input type="hidden" name="_method" id="wizard-method" value="POST">

            <div class="modal-body">

                <div class="wz-progress">
                    <div class="wz-step-dot active" data-step="0"><div class="wz-dot">1</div><span class="wz-dot-label">Personal</span></div>
                    <div class="wz-step-dot" data-step="1"><div class="wz-dot">2</div><span class="wz-dot-label">Position</span></div>
                    <div class="wz-step-dot" data-step="2"><div class="wz-dot">3</div><span class="wz-dot-label">Agency</span></div>
                    <div class="wz-step-dot" data-step="3"><div class="wz-dot">4</div><span class="wz-dot-label">Eligibility</span></div>
                    <div class="wz-step-dot" data-step="4"><div class="wz-dot">5</div><span class="wz-dot-label">Review</span></div>
                </div>

                {{-- STEP 1: Personal --}}
                <div class="step-panel active" id="wz-step-0">
                    <div class="wz-section-head">
                        <div class="wz-icon"><i class="ti ti-user" aria-hidden="true"></i></div>
                        <div><div class="wz-section-title">Personal information</div><div class="wz-section-sub">Basic details about the appointee</div></div>
                    </div>
                    <div class="wz-grid cols3">
                        <div class="wz-field"><label>Last name *</label><input type="text" name="last_name" id="f-last" placeholder="e.g. Dela Cruz" required></div>
                        <div class="wz-field"><label>First name *</label><input type="text" name="first_name" id="f-first" placeholder="e.g. Maria" required></div>
                        <div class="wz-field"><label>Middle name</label><input type="text" name="middle_name" id="f-middle" placeholder="Optional"></div>
                        <div class="wz-field"><label>Extension name</label><input type="text" name="extension_name" id="f-ext" placeholder="Jr., Sr., III"></div>
                        <div class="wz-field"><label>Sex</label>
                            <select name="sex" id="f-sex"><option value="">Select</option><option>Male</option><option>Female</option><option>Prefer not to say</option></select>
                        </div>
                        <div class="wz-field"><label>Date of birth</label><input type="date" name="date_of_birth" id="f-dob"></div>
                        <div class="wz-field"><label>TIN</label><input type="text" name="tin" id="f-tin" placeholder="000-000-000"></div>
                        <div class="wz-field"><label>PWD?</label><select name="pwd" id="f-pwd"><option value="">Select</option><option>Yes</option><option>No</option></select></div>
                        <div class="wz-field"><label>IP group member?</label><select name="ip_group_member" id="f-ip"><option value="">Select</option><option>Yes</option><option>No</option></select></div>
                        <div class="wz-field span3"><label>Ethnicity (if IP group member)</label><input type="text" name="ethnicity" id="f-ethnicity" placeholder="e.g. Tagalog, Ilocano, Maranao"></div>
                    </div>
                </div>

                {{-- STEP 2: Position & Salary --}}
                <div class="step-panel" id="wz-step-1">
                    <div class="wz-section-head">
                        <div class="wz-icon"><i class="ti ti-briefcase" aria-hidden="true"></i></div>
                        <div><div class="wz-section-title">Position and salary</div><div class="wz-section-sub">Role, compensation, and employment details</div></div>
                    </div>
                    <div class="wz-grid">
                        <div class="wz-field span2"><label>Position title *</label><input type="text" name="position_title" id="f-pos" placeholder="e.g. Teacher III" required></div>
                        <div class="wz-field"><label>Position from</label><input type="date" name="position_from" id="f-pfrom"></div>
                        <div class="wz-field"><label>Position to</label><input type="date" name="position_to" id="f-pto"></div>
                        <div class="wz-field"><label>Salary / Pay grade</label>
                            <select name="salary_grade" id="f-sg">
                                <option value="">Select grade</option>
                                @for ($i = 1; $i <= 20; $i++)<option value="SG-{{ $i }}">SG-{{ $i }}</option>@endfor
                            </select>
                        </div>
                        <div class="wz-field"><label>Pay grade step</label>
                            <select name="salary_grade_step" id="f-step">
                                <option value="">Select step</option>
                                @for ($i = 1; $i <= 8; $i++)<option value="Step {{ $i }}">Step {{ $i }}</option>@endfor
                            </select>
                        </div>
                        <div class="wz-field"><label>Monthly salary (₱)</label><input type="text" name="monthly_salary" id="f-sal" placeholder="e.g. 25439.00"></div>
                        <div class="wz-field"><label>Employee status *</label>
                            <select name="employee_status" id="f-estatus" required>
                                <option value="">Select</option><option>Permanent</option><option>Temporary</option><option>Casual</option><option>Contractual</option><option>Coterminous</option>
                            </select>
                        </div>
                        <div class="wz-field span2"><label>Compensation in words (₱)</label><input type="text" name="compensation_words" id="f-salwords" placeholder="e.g. Twenty-five thousand four hundred thirty-nine pesos"></div>
                        <div class="wz-field span2"><label>Compensation in numbers (₱)</label><input type="text" name="compensation_numbers" id="f-salnums" placeholder="e.g. 25439.00"></div>
                        <div class="wz-field"><label>Nature of appointment *</label>
                            <select name="nature_of_appointment" id="f-nature" required>
                                <option value="">Select</option><option>Original</option><option>Promotion</option><option>Transfer</option><option>Reappointment</option><option>Reinstatement</option>
                            </select>
                        </div>
                        <div class="wz-field"><label>Reason (if applicable)</label><input type="text" name="reason" id="f-reason" placeholder="e.g. Transferred to another unit"></div>
                        <div class="wz-field"><label>Position level</label><select name="position_level" id="f-poslevel"><option value="">Select</option><option>First Level</option><option>Second Level</option><option>Third Level</option></select></div>
                        <div class="wz-field"><label>Appointment status</label><select name="appointment_status" id="f-aptstatus"><option value="">Select</option><option>Original</option><option>Renewal</option><option>Reappointment</option></select></div>
                    </div>
                </div>

                {{-- STEP 3: Agency --}}
                <div class="step-panel" id="wz-step-2">
                    <div class="wz-section-head">
                        <div class="wz-icon"><i class="ti ti-building" aria-hidden="true"></i></div>
                        <div><div class="wz-section-title">Agency and administrative details</div><div class="wz-section-sub">Office, plantilla, and records information</div></div>
                    </div>
                    <div class="wz-grid">
                        <div class="wz-field span2"><label>Office / Department / Unit</label><input type="text" name="department" id="f-dept" placeholder="e.g. DepEd – Division of Batangas"></div>
                        <div class="wz-field"><label>School / District</label><input type="text" name="school_district" id="f-school" placeholder="e.g. Batangas NHS"></div>
                        <div class="wz-field"><label>Sector</label><select name="sector" id="f-sector"><option value="">Select</option><option>Education</option><option>Health</option><option>Public Works</option><option>Agriculture</option><option>Social Welfare</option><option>Others</option></select></div>
                        <div class="wz-field span2"><label>Name of agency</label><input type="text" name="agency_name" id="f-agency" placeholder="e.g. Department of Education"></div>
                        <div class="wz-field"><label>Plantilla item number</label><input type="text" name="plantilla_item_number" id="f-plantilla-item" placeholder="e.g. OSEC-DECSB-T3-123456"></div>
                        <div class="wz-field"><label>Plantilla page number</label><input type="text" name="plantilla_page_number" id="f-plantilla-page" placeholder="e.g. 12"></div>
                        <div class="wz-field"><label>ODC number</label><input type="text" name="odc_number" id="f-odc"></div>
                        <div class="wz-field"><label>Date received by records unit</label><input type="date" name="date_received_records" id="f-drec"></div>
                        <div class="wz-field"><label>Date received by HR unit</label><input type="date" name="date_received_hr" id="f-dhr"></div>
                        <div class="wz-field"><label>Previous incumbent</label><input type="text" name="previous_incumbent" id="f-prev" placeholder="Full name"></div>
                        <div class="wz-field"><label>Incumbent</label><input type="text" name="incumbent" id="f-incumbent" placeholder="Full name"></div>
                        <div class="wz-field"><label>Publication mode</label><select name="publication_mode" id="f-pubmode"><option value="">Select</option><option>CSC Bulletin</option><option>Agency Bulletin</option><option>Newspaper</option><option>Online</option><option>Not applicable</option></select></div>
                    </div>
                </div>

                {{-- STEP 4: Eligibility --}}
                <div class="step-panel" id="wz-step-3">
                    <div class="wz-section-head">
                        <div class="wz-icon"><i class="ti ti-certificate" aria-hidden="true"></i></div>
                        <div><div class="wz-section-title">Eligibility and appointment history</div><div class="wz-section-sub">Civil service eligibility and key dates</div></div>
                    </div>
                    <div class="wz-grid">
                        <div class="wz-field span2"><label>Type of eligibility used</label><input type="text" name="eligibility_type" id="f-elig" placeholder="e.g. Professional Teacher – Elementary"></div>
                        <div class="wz-field"><label>Date of validity of eligibility</label><input type="date" name="eligibility_validity" id="f-eligvalid"></div>
                        <div class="wz-field"><label>First time used of eligibility?</label><select name="eligibility_first_used" id="f-eligfirst"><option value="">Select</option><option>Yes</option><option>No</option></select></div>
                        <div class="wz-field"><label>Date of original appointment</label><input type="date" name="date_original_appointment" id="f-doa"></div>
                        <div class="wz-field"><label>Date of last promotion</label><input type="date" name="date_last_promotion" id="f-dlp"></div>
                    </div>
                </div>

                {{-- STEP 5: Review --}}
                <div class="step-panel" id="wz-step-4">
                    <div class="wz-section-head">
                        <div class="wz-icon"><i class="ti ti-clipboard-check" aria-hidden="true"></i></div>
                        <div><div class="wz-section-title">Review and submit</div><div class="wz-section-sub">Check all entries before saving</div></div>
                    </div>
                    <div class="review-grid" id="wz-review-content"></div>
                </div>

                <div class="wz-footer" id="wz-footer">
                    <span class="wz-counter" id="wz-counter">Step 1 of 4</span>
                    <div style="display:flex;gap:8px">
                        <button type="button" class="btn btn-secondary" id="wz-btn-back" style="display:none" onclick="wzGoBack()">
                            <i class="ti ti-arrow-left" style="font-size:13px" aria-hidden="true"></i> Back
                        </button>
                        <button type="button" class="btn btn-primary" id="wz-btn-next" onclick="wzGoNext()">
                            Next <i class="ti ti-arrow-right" style="font-size:13px" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let wzCurrent = 0;
const WZ_TOTAL = 4;

function openWizard() {
    document.getElementById('wizard-form').reset();
    document.getElementById('wizard-form').action = "{{ route('appointments.store') }}";
    document.getElementById('wizard-method').value = 'POST';
    document.getElementById('wizard-modal-title').textContent = 'Add new appointment';
    wzCurrent = 0;
    wzUpdateUI();
    document.getElementById('overlay-wizard').classList.add('show');
}

// Hook for the Edit button — in a real app this would fetch the record via AJAX
// and populate the fields, then point the form at the update route.
function openEditWizard(id) {
    document.getElementById('wizard-form').action = "/appointments/" + id;
    document.getElementById('wizard-method').value = 'PUT';
    document.getElementById('wizard-modal-title').textContent = 'Edit appointment';
    wzCurrent = 0;
    wzUpdateUI();
    document.getElementById('overlay-wizard').classList.add('show');
    // TODO: fetch(`/appointments/${id}/edit-data`).then(...).then(populateForm)
}

function wg(id) {
    const el = document.getElementById(id);
    if (!el) return '—';
    if (el.tagName === 'SELECT') return el.options[el.selectedIndex]?.text || '—';
    return el.value.trim() || '—';
}

function wzBuildReview() {
    const sections = [
        { title: 'Personal information', rows: [
            ['Last name', wg('f-last')], ['First name', wg('f-first')], ['Middle name', wg('f-middle')],
            ['Extension', wg('f-ext')], ['Sex', wg('f-sex')], ['Date of birth', wg('f-dob')],
            ['TIN', wg('f-tin')], ['PWD?', wg('f-pwd')], ['IP group?', wg('f-ip')], ['Ethnicity', wg('f-ethnicity')]
        ]},
        { title: 'Position and salary', rows: [
            ['Position title', wg('f-pos')], ['Position from–to', wg('f-pfrom') + ' → ' + wg('f-pto')],
            ['Pay grade / step', wg('f-sg') + ' / ' + wg('f-step')], ['Monthly salary', wg('f-sal')],
            ['Employee status', wg('f-estatus')], ['Compensation (words)', wg('f-salwords')],
            ['Nature of appointment', wg('f-nature')], ['Reason', wg('f-reason')],
            ['Position level', wg('f-poslevel')], ['Appointment status', wg('f-aptstatus')]
        ]},
        { title: 'Agency and administrative', rows: [
            ['Office / Dept / Unit', wg('f-dept')], ['School / District', wg('f-school')],
            ['Sector', wg('f-sector')], ['Agency', wg('f-agency')],
            ['Plantilla item', wg('f-plantilla-item')], ['Plantilla page', wg('f-plantilla-page')],
            ['ODC number', wg('f-odc')], ['Date received (records)', wg('f-drec')],
            ['Date received (HR)', wg('f-dhr')], ['Previous incumbent', wg('f-prev')],
            ['Incumbent', wg('f-incumbent')], ['Publication mode', wg('f-pubmode')]
        ]},
        { title: 'Eligibility and history', rows: [
            ['Type of eligibility', wg('f-elig')], ['Validity', wg('f-eligvalid')],
            ['First time used?', wg('f-eligfirst')], ['Date of original appointment', wg('f-doa')],
            ['Date of last promotion', wg('f-dlp')]
        ]}
    ];
    document.getElementById('wz-review-content').innerHTML = sections.map(s => `
        <div>
            <div class="review-group-title">${s.title}</div>
            ${s.rows.map(r => `<div class="review-row"><span class="review-key">${r[0]}</span><span class="review-val">${r[1]}</span></div>`).join('')}
        </div>`).join('');
}

function wzGoNext() {
    if (wzCurrent === 0 && (!document.getElementById('f-last').value || !document.getElementById('f-first').value)) {
        alert('Please fill in the required fields (Last name, First name) before continuing.');
        return;
    }
    if (wzCurrent === 1 && (!document.getElementById('f-pos').value || !document.getElementById('f-estatus').value || !document.getElementById('f-nature').value)) {
        alert('Please fill in the required fields (Position title, Employee status, Nature of appointment) before continuing.');
        return;
    }
    if (wzCurrent === WZ_TOTAL - 1) wzBuildReview();
    if (wzCurrent === WZ_TOTAL) {
        document.getElementById('wizard-form').submit();
        return;
    }
    wzCurrent++;
    wzUpdateUI();
}
function wzGoBack() { if (wzCurrent > 0) { wzCurrent--; wzUpdateUI(); } }

function wzUpdateUI() {
    for (let i = 0; i <= 4; i++) {
        const p = document.getElementById('wz-step-' + i);
        if (p) p.classList.toggle('active', i === wzCurrent);
    }
    document.querySelectorAll('.wz-step-dot').forEach(d => {
        d.classList.remove('active', 'done');
        const si = parseInt(d.dataset.step);
        if (si < wzCurrent) d.classList.add('done');
        if (si === wzCurrent) d.classList.add('active');
        const dot = d.querySelector('.wz-dot');
        if (d.classList.contains('done')) dot.innerHTML = '<i class="ti ti-check" style="font-size:12px" aria-hidden="true"></i>';
        else dot.textContent = si + 1;
    });
    const back = document.getElementById('wz-btn-back');
    const next = document.getElementById('wz-btn-next');
    const counter = document.getElementById('wz-counter');
    back.style.display = wzCurrent > 0 ? 'inline-flex' : 'none';
    if (wzCurrent === WZ_TOTAL) {
        counter.textContent = 'Review your entries';
        next.innerHTML = 'Submit appointment <i class="ti ti-send" style="font-size:13px" aria-hidden="true"></i>';
    } else {
        counter.textContent = 'Step ' + (wzCurrent + 1) + ' of ' + WZ_TOTAL;
        next.innerHTML = 'Next <i class="ti ti-arrow-right" style="font-size:13px" aria-hidden="true"></i>';
    }
}

// Re-open the wizard automatically if validation failed server-side
@if ($errors->any())
    document.addEventListener('DOMContentLoaded', () => {
        wzCurrent = 0;
        wzUpdateUI();
        document.getElementById('overlay-wizard').classList.add('show');
    });
@endif
</script>