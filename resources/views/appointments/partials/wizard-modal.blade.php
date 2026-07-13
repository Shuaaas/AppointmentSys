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
                    <div class="wz-step-dot active" data-step="0"><div class="wz-dot">1</div><span class="wz-dot-label">Appointment</span></div>
                    <div class="wz-step-dot" data-step="1"><div class="wz-dot">2</div><span class="wz-dot-label">Checklist</span></div>
                    <div class="wz-step-dot" data-step="2"><div class="wz-dot">3</div><span class="wz-dot-label">RAI</span></div>
                    <div class="wz-step-dot" data-step="3"><div class="wz-dot">4</div><span class="wz-dot-label">Final Deliberation</span></div>
                    <div class="wz-step-dot" data-step="4"><div class="wz-dot">5</div><span class="wz-dot-label">Review</span></div>
                </div>

                {{-- STEP 1: Appointment --}}
                <div class="step-panel active" id="wz-step-0">
                    <div class="wz-section-head">
                        <div class="wz-icon"><i class="ti ti-file-text" aria-hidden="true"></i></div>
                        <div><div class="wz-section-title">Appointment</div><div class="wz-section-sub">Core appointment details</div></div>
                    </div>
                    <div class="wz-grid cols3">
                        <div class="wz-field"><label>Employee last name *</label><input type="text" name="last_name" id="f-last" placeholder="e.g. Dela Cruz" required></div>
                        <div class="wz-field"><label>Employee first name *</label><input type="text" name="first_name" id="f-first" placeholder="e.g. Maria" required></div>
                        <div class="wz-field"><label>Middle name</label><input type="text" name="middle_name" id="f-middle" placeholder="Optional"></div>

                        <div class="wz-field"><label>Extension name</label><input type="text" name="extension_name" id="f-ext" placeholder="Jr., Sr., III"></div>
                        <div class="wz-field span2"><label>Position *</label><input type="text" name="position_title" id="f-pos" placeholder="e.g. Teacher III" required></div>

                        <div class="wz-field"><label>Salary grade</label>
                            <select name="salary_grade" id="f-sg">
                                <option value="">Select grade</option>
                                @for ($i = 1; $i <= 20; $i++)<option value="SG-{{ $i }}">SG-{{ $i }}</option>@endfor
                            </select>
                        </div>
                        <div class="wz-field"><label>Employment status *</label>
                            <select name="employee_status" id="f-estatus" required>
                                <option value="">Select</option><option>Permanent</option><option>Temporary</option><option>Casual</option><option>Contractual</option><option>Coterminous</option>
                            </select>
                        </div>
                        <div class="wz-field"><label>District</label><input type="text" name="school_district" id="f-school" placeholder="e.g. Batangas NHS"></div>

                        <div class="wz-field"><label>School</label><input type="text" name="school" id="f-school-new" placeholder="e.g. Batangas National High School"></div>
                        <div class="wz-field"><label>Plantilla number</label><input type="text" name="plantilla_item_number" id="f-plantilla-item" placeholder="e.g. OSEC-DECSB-T3-123456"></div>
                        <div class="wz-field"><label>Page number</label><input type="text" name="plantilla_page_number" id="f-plantilla-page" placeholder="e.g. 12"></div>

                        <div class="wz-field span3"><label>Salary in words (₱)</label><input type="text" name="compensation_words" id="f-salwords" placeholder="e.g. Twenty-five thousand four hundred thirty-nine pesos"></div>

                        <div class="wz-field span2"><label>Salary in numbers (₱)</label><input type="text" name="compensation_numbers" id="f-salnums" placeholder="e.g. 25439.00"></div>
                        <div class="wz-field"><label>Appointment nature *</label>
                            <select name="nature_of_appointment" id="f-nature" required>
                                <option value="">Select</option><option>Original</option><option>Promotion</option><option>Transfer</option><option>Reappointment</option><option>Reinstatement</option>
                            </select>
                        </div>

                        <div class="wz-field"><label>Vice</label><input type="text" name="previous_incumbent" id="f-prev" placeholder="Full name"></div>
                        <div class="wz-field"><label>Natural vacancy</label><input type="text" name="natural_vacancy" id="f-natural" placeholder="e.g. Yes / No"></div>
                        <div class="wz-field"><label>Date of appointment</label><input type="date" name="date_original_appointment" id="f-doa"></div>

                        <div class="wz-field"><label>Date of signing</label><input type="date" name="date_of_signing" id="f-dosign"></div>
                        <div class="wz-field"><label>Date of validity</label><input type="date" name="eligibility_validity" id="f-eligvalid"></div>
                    </div>
                </div>

                {{-- STEP 2: Checklist --}}
                <div class="step-panel" id="wz-step-1">
                    <div class="wz-section-head">
                        <div class="wz-icon"><i class="ti ti-checklist" aria-hidden="true"></i></div>
                        <div><div class="wz-section-title">Checklist</div><div class="wz-section-sub">Education and senior high school</div></div>
                    </div>
                    <div class="wz-grid">
                        <p class="wz-grid-note text-muted">These fields are auto-filled from the appointment and cannot be edited.</p>
                        <div class="wz-field span2"><label>Employee name</label><input type="text" class="wz-readonly" id="rx-cl-empname" readonly tabindex="-1"></div>
                        <div class="wz-field"><label>Position</label><input type="text" class="wz-readonly" id="rx-cl-pos" readonly tabindex="-1"></div>
                        <div class="wz-field"><label>Salary grade</label><input type="text" class="wz-readonly" id="rx-cl-sg" readonly tabindex="-1"></div>
                        <div class="wz-field"><label>Salary number</label><input type="text" class="wz-readonly" id="rx-cl-salnum" readonly tabindex="-1"></div>
                        <div class="wz-field"><label>Date of signing</label><input type="text" class="wz-readonly" id="rx-cl-dosign" readonly tabindex="-1"></div>
                        <div class="wz-field span2"><label>Education (e.g. Bachelor of ...)</label><input type="text" name="education" id="f-education" placeholder="e.g. Bachelor of Secondary Education"></div>
                        <div class="wz-field"><label>Senior high school?</label>
                            <select name="senior_high_school" id="f-shs">
                                <option value="">Select</option><option value="Yes">Yes</option><option value="No">No</option>
                            </select>
                        </div>
                        <div class="wz-field" id="strand-field" style="display:none"><label>Strand</label>
                            <select name="senior_high_strand" id="f-strand">
                                <option value="">Select strand</option><option value="STEM">STEM</option><option value="HUMMS">HUMMS</option><option value="ABM">ABM</option><option value="TVL">TVL</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- STEP 3: RAI --}}
                <div class="step-panel" id="wz-step-2">
                    <div class="wz-section-head">
                        <div class="wz-icon"><i class="ti ti-file-text" aria-hidden="true"></i></div>
                        <div><div class="wz-section-title">RAI</div><div class="wz-section-sub">Report on Appointment Issued</div></div>
                    </div>
                    <div class="wz-grid">
                        <p class="wz-grid-note text-muted">These fields are auto-filled from the appointment and cannot be edited.</p>
                        <div class="wz-field span2"><label>Employee name</label><input type="text" class="wz-readonly" id="rx-rai-empname" readonly tabindex="-1"></div>
                        <div class="wz-field"><label>Position</label><input type="text" class="wz-readonly" id="rx-rai-pos" readonly tabindex="-1"></div>
                        <div class="wz-field"><label>Plantilla number</label><input type="text" class="wz-readonly" id="rx-rai-plantilla" readonly tabindex="-1"></div>
                        <div class="wz-field"><label>Salary grade</label><input type="text" class="wz-readonly" id="rx-rai-sg" readonly tabindex="-1"></div>
                        <div class="wz-field"><label>Salary number</label><input type="text" class="wz-readonly" id="rx-rai-salnum" readonly tabindex="-1"></div>
                        <div class="wz-field"><label>Employment status</label><input type="text" class="wz-readonly" id="rx-rai-estatus" readonly tabindex="-1"></div>
                        <div class="wz-field"><label>Appointment nature</label><input type="text" class="wz-readonly" id="rx-rai-nature" readonly tabindex="-1"></div>
                    </div>
                </div>

                {{-- STEP 4: Final Deliberation --}}
                <div class="step-panel" id="wz-step-3">
                    <div class="wz-section-head">
                        <div class="wz-icon"><i class="ti ti-gavel" aria-hidden="true"></i></div>
                        <div><div class="wz-section-title">Final Deliberation</div><div class="wz-section-sub">Non-teaching determination</div></div>
                    </div>
                    <div class="wz-grid">
                        <p class="wz-grid-note text-muted">These fields are auto-filled from the appointment and cannot be edited.</p>
                        <div class="wz-field span2"><label>Employee name</label><input type="text" class="wz-readonly" id="rx-fd-empname" readonly tabindex="-1"></div>
                        <div class="wz-field"><label>Position</label><input type="text" class="wz-readonly" id="rx-fd-pos" readonly tabindex="-1"></div>
                        <div class="wz-field"><label>Date of signing</label><input type="text" class="wz-readonly" id="rx-fd-dosign" readonly tabindex="-1"></div>
                        <div class="wz-field"><label>School</label><input type="text" class="wz-readonly" id="rx-fd-school" readonly tabindex="-1"></div>
                        <div class="wz-field"><label>Non teaching?</label>
                            <select name="non_teaching" id="f-nonteaching">
                                <option value="">Select</option><option value="Yes">Yes</option><option value="No">No</option>
                            </select>
                        </div>
                        <div class="wz-field" id="fd-result-field" style="display:none">
                            <label>Result</label>
                            <div id="fd-result" style="padding:10px 13px;border:1px solid var(--border);border-radius:8px;font-weight:600;background:var(--accent-light);color:var(--text-primary)"></div>
                        </div>
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
    syncChecklist();
    syncFinalDeliberation();
    syncReadonly();
    document.getElementById('overlay-wizard').classList.add('show');
}

function wg(id) {
    const el = document.getElementById(id);
    if (!el) return '—';
    if (el.tagName === 'SELECT') return el.options[el.selectedIndex]?.text || '—';
    return el.value.trim() || '—';
}

function rxv(id) {
    const el = document.getElementById(id);
    if (!el) return '—';
    return el.value.trim() || '—';
}

function wzEmployeeName() {
    const name = (wg('f-first') + ' ' + wg('f-middle') + ' ' + wg('f-last') + ' ' + wg('f-ext'))
        .replace(/\s+/g, ' ').trim();
    return name || '—';
}

function syncReadonly() {
    const name = wzEmployeeName();
    const set = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.value = (val && val !== '—') ? val : '';
    };
    set('rx-cl-empname', name);  set('rx-cl-pos', wg('f-pos'));
    set('rx-cl-sg', wg('f-sg'));  set('rx-cl-salnum', wg('f-salnums'));
    set('rx-cl-dosign', wg('f-dosign'));

    set('rx-rai-empname', name);  set('rx-rai-pos', wg('f-pos'));
    set('rx-rai-plantilla', wg('f-plantilla-item'));  set('rx-rai-sg', wg('f-sg'));
    set('rx-rai-salnum', wg('f-salnums'));  set('rx-rai-estatus', wg('f-estatus'));
    set('rx-rai-nature', wg('f-nature'));

    set('rx-fd-empname', name);  set('rx-fd-pos', wg('f-pos'));
    set('rx-fd-dosign', wg('f-dosign'));  set('rx-fd-school', wg('f-school-new'));
}

function fdResult() {
    const v = document.getElementById('f-nonteaching')?.value;
    return v === 'Yes' ? 'Tommy' : (v === 'No' ? 'Ruben' : '—');
}

function wzBuildReview() {
    const sections = [
        { title: 'Appointment', rows: [
            ['Employee name', (wg('f-first') + ' ' + wg('f-middle') + ' ' + wg('f-last') + ' ' + wg('f-ext')).replace(/\s+/g, ' ').trim()],
            ['Position', wg('f-pos')], ['Salary grade', wg('f-sg')],
            ['Employment status', wg('f-estatus')], ['District', wg('f-school')], ['School', wg('f-school-new')],
            ['Salary in words', wg('f-salwords')], ['Salary in numbers', wg('f-salnums')],
            ['Appointment nature', wg('f-nature')], ['Vice', wg('f-prev')],
            ['Natural vacancy', wg('f-natural')], ['Plantilla number', wg('f-plantilla-item')],
            ['Page number', wg('f-plantilla-page')], ['Date of appointment', wg('f-doa')],
            ['Date of signing', wg('f-dosign')], ['Date of validity', wg('f-eligvalid')]
        ]},
        { title: 'Checklist', rows: [
            ['Employee name', rxv('rx-cl-empname')], ['Position', rxv('rx-cl-pos')],
            ['Salary grade', rxv('rx-cl-sg')], ['Salary number', rxv('rx-cl-salnum')],
            ['Date of signing', rxv('rx-cl-dosign')],
            ['Education', wg('f-education')], ['Senior high school?', wg('f-shs')], ['Strand', wg('f-strand')]
        ]},
        { title: 'RAI', rows: [
            ['Employee name', rxv('rx-rai-empname')], ['Position', rxv('rx-rai-pos')],
            ['Plantilla number', rxv('rx-rai-plantilla')], ['Salary grade', rxv('rx-rai-sg')],
            ['Salary number', rxv('rx-rai-salnum')], ['Employment status', rxv('rx-rai-estatus')],
            ['Appointment nature', rxv('rx-rai-nature')]
        ]},
        { title: 'Final Deliberation', rows: [
            ['Employee name', rxv('rx-fd-empname')], ['Position', rxv('rx-fd-pos')],
            ['Date of signing', rxv('rx-fd-dosign')], ['School', rxv('rx-fd-school')],
            ['Non teaching?', wg('f-nonteaching')], ['Result', fdResult()]
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
    if (wzCurrent === 0 && (!document.getElementById('f-pos').value || !document.getElementById('f-estatus').value || !document.getElementById('f-nature').value)) {
        alert('Please fill in the required fields (Position, Employment status, Appointment nature) before continuing.');
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

function syncChecklist() {
    const shs = document.getElementById('f-shs');
    const strand = document.getElementById('strand-field');
    if (shs && strand) strand.style.display = shs.value === 'Yes' ? 'block' : 'none';
}
function syncFinalDeliberation() {
    const nt = document.getElementById('f-nonteaching');
    const field = document.getElementById('fd-result-field');
    const result = document.getElementById('fd-result');
    if (nt && field && result) {
        const show = nt.value === 'Yes' || nt.value === 'No';
        field.style.display = show ? 'block' : 'none';
        result.textContent = fdResult();
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

const wzForm = document.getElementById('wizard-form');
if (wzForm) {
    wzForm.addEventListener('input', syncReadonly);
    wzForm.addEventListener('change', syncReadonly);
}
</script>
