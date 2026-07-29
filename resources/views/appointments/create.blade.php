@extends('layout.app')

@section('title', 'New Appointment')

@section('content')
    <div class="page-panel">
        <div class="card">
            <div class="card-body">
                <form id="wizard-form" class="wizard-page" method="POST" action="{{ route('appointments.store') }}">
                    @csrf
                    <input type="hidden" name="_method" value="POST">

                    <div class="wz-progress">
                        <div class="wz-step-dot active" data-step="0"><div class="wz-dot"><span class="wz-num">1</span><span class="wz-dot-label">Appointment</span></div></div>
                        <div class="wz-step-dot" data-step="1"><div class="wz-dot"><span class="wz-num">2</span><span class="wz-dot-label">Checklist</span></div></div>
                        <div class="wz-step-dot" data-step="2"><div class="wz-dot"><span class="wz-num">3</span><span class="wz-dot-label">RAI</span></div></div>
                        <div class="wz-step-dot" data-step="3"><div class="wz-dot"><span class="wz-num">4</span><span class="wz-dot-label">Final Deliberation</span></div></div>
                        <div class="wz-step-dot" data-step="4"><div class="wz-dot"><span class="wz-num">5</span><span class="wz-dot-label">Monitoring Data</span></div></div>
                        <div class="wz-step-dot" data-step="5"><div class="wz-dot"><span class="wz-num">6</span><span class="wz-dot-label">REVIEW</span></div></div>
                    </div>

                    {{-- STEP 1: Appointment --}}
                    <div class="step-panel active" id="wz-step-0">
                        <div class="wz-grid cols3">
                            <div class="wz-field"><label>Employee last name <span class="req">*</span></label><input type="text" name="last_name" id="f-last" class="alpha-only" placeholder="e.g. Dela Cruz" value="{{ old('last_name') }}" required></div>
                            <div class="wz-field"><label>Employee first name <span class="req">*</span></label><input type="text" name="first_name" id="f-first" class="alpha-only" placeholder="e.g. Maria" value="{{ old('first_name') }}" required></div>
                            <div class="wz-field"><label>Middle name</label><input type="text" name="middle_name" id="f-middle" class="alpha-only" placeholder="Optional" value="{{ old('middle_name') }}"></div>

                            <div class="wz-field"><label>Extension name</label><input type="text" name="extension_name" id="f-ext" class="alpha-only" placeholder="Jr., Sr., III" value="{{ old('extension_name') }}"></div>
                            <div class="wz-field span2" style="position:relative"><label>Position <span class="req">*</span></label><input type="text" name="position_title" id="f-pos" placeholder="e.g. Teacher III" value="{{ old('position_title') }}" required autocomplete="off"><div id="position-dropdown" class="plantilla-dropdown" style="display:none"></div></div>

                            <div class="wz-field-pair">
                                <div class="wz-field"><label>Salary grade</label>
                                    <select name="salary_grade" id="f-sg">
                                        <option value="">Select grade</option>
                                        @for ($i = 1; $i <= 30; $i++)<option value="{{ $i }}" {{ old('salary_grade') == $i ? 'selected' : '' }}>{{ $i }}</option>@endfor
                                    </select>
                                </div>
                                <div class="wz-field"><label>Step</label>
                                    <select name="salary_grade_step" id="f-step">
                                        <option value="">Select step</option>
                                        @for ($i = 1; $i <= 8; $i++)<option value="{{ $i }}" {{ old('salary_grade_step') == $i ? 'selected' : '' }}>{{ $i }}</option>@endfor
                                    </select>
                                </div>
                            </div>
                            <div class="wz-field"><label>Employment status <span class="req">*</span></label>
                                <select name="employee_status" id="f-estatus" required>
                                    <option value="">Select</option><option value="Permanent" {{ old('employee_status') === 'Permanent' ? 'selected' : '' }}>Permanent</option><option value="Substitute" {{ old('employee_status') === 'Substitute' ? 'selected' : '' }}>Substitute</option><option value="Provisional" {{ old('employee_status') === 'Provisional' ? 'selected' : '' }}>Provisional</option>
                                </select>
                            </div>
                            <div class="wz-field" style="position:relative"><label>District</label><input type="text" name="school_district" id="f-school" placeholder="e.g. Naic ll" value="{{ old('school_district') }}" autocomplete="off"><div id="district-dropdown" class="plantilla-dropdown" style="display:none"></div></div>

                            <div class="wz-field" style="position:relative"><label>School</label><input type="text" name="school" id="f-school-new" placeholder="e.g. Centro De Naic NHS" value="{{ old('school') }}" autocomplete="off"><div id="school-dropdown" class="plantilla-dropdown" style="display:none"></div></div>
                            <div class="wz-field" style="position:relative">
                                <label>Plantilla number</label>
                                <input type="text" name="plantilla_item_number" id="f-plantilla-item" placeholder="e.g. OSEC-DECSB-T3-123456" value="{{ old('plantilla_item_number') }}" autocomplete="off">
                                <div id="plantilla-dropdown" class="plantilla-dropdown" style="display:none"></div>
                            </div>
                            <div class="wz-field"><label>Page number</label><input type="text" name="plantilla_page_number" id="f-plantilla-page" placeholder="e.g. 12" value="{{ old('plantilla_page_number') }}"></div>

                            <div class="wz-field span2"><label>Salary in words (₱)</label><input type="text" name="compensation_words" id="f-salwords" placeholder="e.g. Twenty-five thousand four hundred thirty-nine pesos" value="{{ old('compensation_words') }}" readonly></div>
                            <div class="wz-field"><label>Salary in numbers (₱)</label><input type="text" name="compensation_numbers" id="f-salnums" placeholder="e.g. 25439.00" value="{{ old('compensation_numbers') }}" readonly></div>

                            <div class="wz-field"><label>Nature of Appointment <span class="req">*</span></label>
                                <select name="nature_of_appointment" id="f-nature" required>
                                    <option value="">Select</option><option value="Original" {{ old('nature_of_appointment') === 'Original' ? 'selected' : '' }}>Original</option><option value="Promotion" {{ old('nature_of_appointment') === 'Promotion' ? 'selected' : '' }}>Promotion</option><option value="Demotion" {{ old('nature_of_appointment') === 'Demotion' ? 'selected' : '' }}>Demotion</option><option value="Transfer" {{ old('nature_of_appointment') === 'Transfer' ? 'selected' : '' }}>Transfer</option><option value="Re-Classification" {{ old('nature_of_appointment') === 'Re-Classification' ? 'selected' : '' }}>Re-Classification</option><option value="Re-Employment" {{ old('nature_of_appointment') === 'Re-Employment' ? 'selected' : '' }}>Re-Employment</option><option value="Re-Appointment" {{ old('nature_of_appointment') === 'Re-Appointment' ? 'selected' : '' }}>Re-Appointment</option>
                                </select>
                            </div>

                            <div class="wz-field"><label>Incumbent</label><input type="text" name="incumbent" id="f-incumbent" placeholder="Full name" value="{{ old('incumbent') }}"></div>
                            <div class="wz-field"><label>Reason of Incumbent</label><input type="text" name="natural_vacancy" id="f-natural" placeholder="e.g. Transferred, Promotion,..." value="{{ old('natural_vacancy') }}"></div>

                            <div class="wz-field"><label>Date of signing</label><input type="date" name="date_of_signing" id="f-dosign" value="{{ old('date_of_signing') }}"></div>

                            <div class="wz-date-group" data-status-group="publication">
                                <div class="wz-field"><label>Publication Date (FROM)</label><input type="date" name="publication_date_from" id="f-pubdate-from" value="{{ old('publication_date_from') }}"></div>
                                <div class="wz-na-field" style="display:none"><label>Publication Date (FROM)</label><input type="text" class="wz-na-input" value="Not Applicable" disabled tabindex="-1"></div>
                            </div>

                            <div class="wz-date-group" data-status-group="publication">
                                <div class="wz-field"><label>Publication Date (TO)</label><input type="date" name="publication_date_to" id="f-pubdate-to" value="{{ old('publication_date_to') }}"></div>
                                <div class="wz-na-field" style="display:none"><label>Publication Date (TO)</label><input type="text" class="wz-na-input" value="Not Applicable" disabled tabindex="-1"></div>
                            </div>

                            <div class="wz-date-group" data-status-group="assessment">
                                <div class="wz-field"><label>Assessment Date</label><input type="date" name="assessment_date" id="f-assessment" value="{{ old('assessment_date') }}"></div>
                                <div class="wz-na-field" style="display:none"><label>Assessment Date</label><input type="text" class="wz-na-input" value="Not Applicable" disabled tabindex="-1"></div>
                            </div>

                            <div class="wz-date-group" data-status-group="deliberation">
                                <div class="wz-field"><label>Deliberation Date</label><input type="date" name="deliberation_date" id="f-deliberation" value="{{ old('deliberation_date') }}"></div>
                                <div class="wz-na-field" style="display:none"><label>Deliberation Date</label><input type="text" class="wz-na-input" value="Not Applicable" disabled tabindex="-1"></div>
                            </div>
                            <div class="wz-field"><label>TIN</label><input type="text" name="tin" id="f-tin" placeholder="9 digits" value="{{ old('tin') }}" maxlength="9"></div>
                        </div>
                    </div>

                    {{-- STEP 2: Checklist --}}
                    <div class="step-panel" id="wz-step-1">
                        <div class="wz-grid">
                            <p class="wz-grid-note text-muted">These fields are auto-filled from the appointment and cannot be edited.</p>
                            <div class="wz-field span2"><label>Employee name</label><input type="text" class="wz-readonly" id="rx-cl-empname" readonly tabindex="-1"></div>
                            <div class="wz-field"><label>Position</label><input type="text" class="wz-readonly" id="rx-cl-pos" readonly tabindex="-1"></div>
                            <div class="wz-field"><label>Salary grade</label><input type="text" class="wz-readonly" id="rx-cl-sg" readonly tabindex="-1"></div>
                            <div class="wz-field"><label>Salary number</label><input type="text" class="wz-readonly" id="rx-cl-salnum" readonly tabindex="-1"></div>
                            <div class="wz-field"><label>Date of signing</label><input type="text" class="wz-readonly" id="rx-cl-dosign" readonly tabindex="-1"></div>
                            <div class="wz-field span2"><label>Education (e.g. Bachelor of ...)</label><input type="text" name="education" id="f-education" placeholder="e.g. Bachelor of Secondary Education" value="{{ old('education') }}"></div>
                            <div class="wz-field"><label>Senior high school?</label>
                                <select name="senior_high_school" id="f-shs">
                                    <option value="">Select</option><option value="Yes" {{ old('senior_high_school') === 'Yes' ? 'selected' : '' }}>Yes</option><option value="No" {{ old('senior_high_school') === 'No' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="wz-field" id="strand-field" style="display:none"><label>Strand</label>
                                <select name="senior_high_strand" id="f-strand">
                                    <option value="">Select strand</option>
                                    <option value="ABM" {{ old('senior_high_strand') === 'ABM' ? 'selected' : '' }}>ABM</option>
                                    <option value="HUMSS" {{ old('senior_high_strand') === 'HUMSS' ? 'selected' : '' }}>HUMSS</option>
                                    <option value="STEM" {{ old('senior_high_strand') === 'STEM' ? 'selected' : '' }}>STEM</option>
                                    <option value="TVL Track" {{ old('senior_high_strand') === 'SHS - TVL Track' ? 'selected' : '' }}>SHS - TVL Track</option>
                                    <option value="Sports Track" {{ old('senior_high_strand') === 'SHS - Sports Track' ? 'selected' : '' }}>SHS - Sports Track</option>
                                </select>
                            </div>
                            <div class="wz-field"><label>Eligibility</label>
                                <select name="eligibility_type" id="f-elig">
                                    <option value="">Select</option><option value="LET" {{ old('eligibility_type') === 'LET' ? 'selected' : '' }}>LET</option><option value="PVET" {{ old('eligibility_type') === 'PVET' ? 'selected' : '' }}>PVET</option>
                                </select>
                            </div>
                            <div class="wz-field"><label>Date of Validity of Eligibility</label><input type="date" name="eligibility_validity" id="f-eligvalid" value="{{ old('eligibility_validity') }}"></div>
                            <div class="wz-field"><label>First time used of Eligibility?</label>
                                <select name="eligibility_first_used" id="f-eligfirst">
                                    <option value="">Select</option><option value="Yes" {{ old('eligibility_first_used') === 'Yes' ? 'selected' : '' }}>Yes</option><option value="No" {{ old('eligibility_first_used') === 'No' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- STEP 3: RAI --}}
                    <div class="step-panel" id="wz-step-2">
                        <div class="wz-grid">
                            <p class="wz-grid-note text-muted">These fields are auto-filled from the appointment and cannot be edited.</p>
                            <div class="wz-field span2"><label>Employee name</label><input type="text" class="wz-readonly" id="rx-rai-empname" readonly tabindex="-1"></div>
                            <div class="wz-field"><label>Position</label><input type="text" class="wz-readonly" id="rx-rai-pos" readonly tabindex="-1"></div>
                            <div class="wz-field"><label>Plantilla number</label><input type="text" class="wz-readonly" id="rx-rai-plantilla" readonly tabindex="-1"></div>
                            <div class="wz-field"><label>Salary grade</label><input type="text" class="wz-readonly" id="rx-rai-sg" readonly tabindex="-1"></div>
                            <div class="wz-field"><label>Salary number</label><input type="text" class="wz-readonly" id="rx-rai-salnum" readonly tabindex="-1"></div>
                            <div class="wz-field"><label>Employment status</label><input type="text" class="wz-readonly" id="rx-rai-estatus" readonly tabindex="-1"></div>
                            <div class="wz-field"><label>Nature of Appointment</label><input type="text" class="wz-readonly" id="rx-rai-nature" readonly tabindex="-1"></div>
                        </div>
                    </div>

                    {{-- STEP 4: Final Deliberation --}}
                    <div class="step-panel" id="wz-step-3">
                        <div class="wz-grid">
                            <p class="wz-grid-note text-muted">These fields are auto-filled from the appointment and cannot be edited.</p>
                            <div class="wz-field span2"><label>Employee name</label><input type="text" class="wz-readonly" id="rx-fd-empname" readonly tabindex="-1"></div>
                            <div class="wz-field"><label>Position</label><input type="text" class="wz-readonly" id="rx-fd-pos" readonly tabindex="-1"></div>
                            <div class="wz-field"><label>Date of signing</label><input type="text" class="wz-readonly" id="rx-fd-dosign" readonly tabindex="-1"></div>
                            <div class="wz-field"><label>School</label><input type="text" class="wz-readonly" id="rx-fd-school" readonly tabindex="-1"></div>
                            <div class="wz-field"><label>Teaching?</label>
                                <select name="non_teaching" id="f-nonteaching">
                                    <option value="">Select</option><option value="Yes" {{ old('non_teaching') === 'Yes' ? 'selected' : '' }}>Yes</option><option value="No" {{ old('non_teaching') === 'No' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="wz-field" id="fd-result-field" style="display:none">
                                <label>Result</label>
                                <div id="fd-result" style="padding:10px 13px;border:1px solid var(--border);border-radius:8px;font-weight:600;background:var(--accent-light);color:var(--text-primary)"></div>
                            </div>
                        </div>
                    </div>

                    {{-- STEP 5: Monitoring Data --}}
                    <div class="step-panel" id="wz-step-4">
                        <div class="wz-grid">
                            <div class="wz-field"><label>Date of Last Promotion</label><input type="date" name="date_last_promotion" id="f-dlp" value="{{ old('date_last_promotion') }}"></div>
                            <div class="wz-field"><label>Position From <span class="req">*</span></label><input type="text" name="position_from" id="f-pfrom" placeholder="e.g. Teacher I" value="{{ old('position_from') }}" required></div>
                            <div class="wz-field span2"><label>Name of Previous Incumbent</label><input type="text" name="previous_incumbent" id="f-prev" placeholder="Full name" value="{{ old('previous_incumbent') }}"></div>
                            <div class="wz-field"><label>Position Level <span class="req">*</span></label>
                                <select name="position_level" id="f-poslevel" required>
                                    <option value="">Select</option><option value="First Level" {{ old('position_level') === 'First Level' ? 'selected' : '' }}>First Level</option><option value="Second Level" {{ old('position_level') === 'Second Level' ? 'selected' : '' }}>Second Level</option><option value="Third Level" {{ old('position_level') === 'Third Level' ? 'selected' : '' }}>Third Level</option>
                                </select>
                            </div>
                            <div class="wz-field"><label>Sex <span class="req">*</span></label>
                                <select name="sex" id="f-sex" required>
                                    <option value="">Select</option><option value="Male" {{ old('sex') === 'Male' ? 'selected' : '' }}>Male</option><option value="Female" {{ old('sex') === 'Female' ? 'selected' : '' }}>Female</option><option value="Prefer not to say" {{ old('sex') === 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                                </select>
                            </div>
                            <div class="wz-field"><label>Date of Birth <span class="req">*</span></label><input type="date" name="date_of_birth" id="f-dob" value="{{ old('date_of_birth') }}" required></div>
                            <div class="wz-field"><label>PWD? <span class="req">*</span></label>
                                <select name="pwd" id="f-pwd" required>
                                    <option value="">Select</option><option value="Yes" {{ old('pwd') === 'Yes' ? 'selected' : '' }}>Yes</option><option value="No" {{ old('pwd') === 'No' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="wz-field"><label>Type of Disability</label><input type="text" name="type_of_disability" id="f-pwdtype" placeholder="e.g. Permanent physical disability" value="{{ old('type_of_disability') }}"></div>
                            <div class="wz-field"><label>Member of IP Group? <span class="req">*</span></label>
                                <select name="ip_group_member" id="f-ip" required>
                                    <option value="">Select</option><option value="Yes" {{ old('ip_group_member') === 'Yes' ? 'selected' : '' }}>Yes</option><option value="No" {{ old('ip_group_member') === 'No' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="wz-field span2"><label>Ethnicity</label><input type="text" name="ethnicity" id="f-ethnicity" placeholder="e.g. Tagalog, Bisaya" value="{{ old('ethnicity') }}"></div>
                        </div>
                    </div>

                    {{-- STEP 6: Review --}}
                    <div class="step-panel" id="wz-step-5">
                        <div class="wz-section-head">
                            <div class="wz-icon"><i class="ti ti-clipboard-check" aria-hidden="true"></i></div>
                            <div><div class="wz-section-title">Review and submit</div><div class="wz-section-sub">Check all entries before saving</div></div>
                        </div>
                        <div class="review-grid" id="wz-review-content"></div>
                    </div>

                    <div class="wz-footer" id="wz-footer">
                        <span class="wz-counter" id="wz-counter">Step 1 of 5</span>
                        <div style="display:flex;gap:8px">
                            <a href="{{ route('appointments.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="button" class="btn btn-secondary" id="wz-btn-back" style="display:none" onclick="wzGoBack()">
                                <i class="ti ti-arrow-left" style="font-size:13px" aria-hidden="true"></i> Back
                            </button>
                            <button type="button" class="btn btn-primary" id="wz-btn-next" onclick="wzGoNext()">
                                Next <i class="ti ti-arrow-right" style="font-size:13px" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="overlay" id="wz-warning-overlay">
        <div class="modal" style="max-width:420px">
            <div class="modal-head">
                <span class="modal-title">Required fields missing</span>
                <button type="button" class="modal-close" onclick="wzCloseWarning()" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="confirm-icon"><i class="ti ti-alert-triangle" style="font-size:44px;color:var(--red)" aria-hidden="true"></i></div>
                <p class="confirm-msg" id="wz-warning-msg"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-blue" onclick="wzCloseWarning()">OK</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
var wzCurrent = 0;
var WZ_TOTAL = 5;

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
        if (el) el.value = val !== undefined ? val : '';
    };

    set('rx-cl-empname', name);  set('rx-cl-pos', wg('f-pos'));
    set('rx-cl-sg', wg('f-sg') && wg('f-step') ? wg('f-sg') + '-' + wg('f-step') : (wg('f-sg') || '—'));  set('rx-cl-salnum', wg('f-salnums'));
    set('rx-cl-dosign', wg('f-dosign'));

    set('rx-rai-empname', name);  set('rx-rai-pos', wg('f-pos'));
    set('rx-rai-plantilla', wg('f-plantilla-item'));  set('rx-rai-sg', wg('f-sg') && wg('f-step') ? wg('f-sg') + '-' + wg('f-step') : (wg('f-sg') || '—'));
    set('rx-rai-salnum', wg('f-salnums'));  set('rx-rai-estatus', wg('f-estatus'));
    set('rx-rai-nature', wg('f-nature'));

    set('rx-fd-empname', name);  set('rx-fd-pos', wg('f-pos'));
    set('rx-fd-dosign', wg('f-dosign'));  set('rx-fd-school', wg('f-school-new'));

    set('rx-md-empname', name);  set('rx-md-pos', wg('f-pos'));
    set('rx-md-dlp', wg('f-dlp'));  set('rx-md-pfrom', wg('f-pfrom'));
    set('rx-md-prev', wg('f-prev') || 'Vacant');
    set('rx-md-poslevel', wg('f-poslevel'));
    set('rx-md-sex', wg('f-sex'));
    set('rx-md-dob', wg('f-dob'));
    const pwdVal = wg('f-pwd');
    set('rx-md-pwd', pwdVal);
    if (pwdVal === 'Yes') {
        set('rx-md-pwdtype', wg('f-pwdtype') || '—');
    } else if (pwdVal === 'No') {
        const pwdTypeEl = document.getElementById('rx-md-pwdtype');
        if (pwdTypeEl && !pwdTypeEl.value) pwdTypeEl.value = 'N/A';
    } else {
        set('rx-md-pwdtype', '');
    }
    set('rx-md-ip', wg('f-ip'));
    set('rx-md-ethnicity', wg('f-ethnicity') || '—');
}

function syncDateFieldsByStatus() {
    const estatus = document.getElementById('f-estatus');
    const groups = document.querySelectorAll('.wz-date-group');
    if (!estatus || !groups.length) return;

    const status = estatus.value.trim().toLowerCase();
    const isNonPermanent = status === 'substitute' || status === 'provisional';

    groups.forEach(group => {
        const input = group.querySelector('.wz-field');
        const naField = group.querySelector('.wz-na-field');
        if (!input || !naField) return;

        if (isNonPermanent) {
            input.style.display = 'none';
            naField.style.display = '';
        } else {
            input.style.display = '';
            naField.style.display = 'none';
        }
    });
}

function fdResult() {
    const v = document.getElementById('f-nonteaching')?.value;
    return v === 'Yes' ? 'Antonio P. Faustino Jr.' : (v === 'No' ? 'Ruben E. Faltado III' : '—');
}

function wzBuildReview() {
    const reviewEl = document.getElementById('wz-review-content');
    if (!reviewEl) return; // guard: only run when review panel is in the DOM
    const sections = [
        { title: 'Appointment', rows: [
            ['Employee name', (wg('f-first') + ' ' + wg('f-middle') + ' ' + wg('f-last') + ' ' + wg('f-ext')).replace(/\s+/g, ' ').trim()],
            ['Position', wg('f-pos')], ['Salary grade', wg('f-sg') && wg('f-step') ? wg('f-sg') + '-' + wg('f-step') : wg('f-sg')],
            ['Employment status', wg('f-estatus')], ['District', wg('f-school')], ['School', wg('f-school-new')],
            ['Salary in words', wg('f-salwords')], ['Salary in numbers', wg('f-salnums')],
            ['Appointment nature', wg('f-nature')], ['Incumbent', wg('f-incumbent') || 'Vacant'],
            ['Reason', wg('f-natural') || 'N/A'], ['Plantilla number', wg('f-plantilla-item')],
            ['Page number', wg('f-plantilla-page')],
            ['Date of signing', wg('f-dosign')],
            ['Publication Date (FROM)', wg('f-pubdate-from')],
            ['Publication Date (TO)', wg('f-pubdate-to')],
            ['Assessment Date', wg('f-assessment')],
            ['Deliberation Date', wg('f-deliberation')]
        ]},
        { title: 'Checklist', rows: [
            ['Employee name', rxv('rx-cl-empname')], ['Position', rxv('rx-cl-pos')],
            ['Salary grade', rxv('rx-cl-sg')], ['Salary number', rxv('rx-cl-salnum')],
            ['Date of signing', rxv('rx-cl-dosign')],
            ['Education', wg('f-education')], ['Senior high school?', wg('f-shs')], ['Strand', wg('f-strand')],
            ['Eligibility', wg('f-elig')], ['Date of Validity', wg('f-eligvalid')],
            ['First time used?', wg('f-eligfirst')]
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
        ]},
        { title: 'Monitoring Data', rows: [
            ['Employee name', wzEmployeeName()], ['Position', wg('f-pos')],
            ['Date of Last Promotion', wg('f-dlp')], ['Position From', wg('f-pfrom')],
            ['Name of Previous Incumbent', wg('f-prev') || 'Vacant'],
            ['Position Level', wg('f-poslevel')],
            ['Sex', wg('f-sex')], ['Date of Birth', wg('f-dob')], ['PWD?', wg('f-pwd')],
            ['Type of Disability', wg('f-pwdtype') || (wg('f-pwd') === 'No' ? 'N/A' : '—')],
            ['Member of IP Group?', wg('f-ip')], ['Ethnicity', wg('f-ethnicity') || '—']
        ]}
    ];
    reviewEl.innerHTML = sections.map(s => `
        <div>
            <div class="review-group-title">${s.title}</div>
            ${s.rows.map(r => `<div class="review-row"><span class="review-key">${r[0]}</span><span class="review-val">${r[1]}</span></div>`).join('')}
        </div>`).join('');
}

function wzGoNext() {
    const fLast    = document.getElementById('f-last');
    const fFirst   = document.getElementById('f-first');
    const fPos     = document.getElementById('f-pos');
    const fEstatus = document.getElementById('f-estatus');
    const fNature  = document.getElementById('f-nature');
    if (wzCurrent === 0 && (!fLast?.value || !fFirst?.value)) {
        wzShowWarning('Please fill in the required fields (Last name, First name) before continuing.');
        return;
    }
    if (wzCurrent === 0 && (!fPos?.value || !fEstatus?.value || !fNature?.value)) {
        wzShowWarning('Please fill in the required fields (Position, Employment status, Appointment nature) before continuing.');
        return;
    }
    if (wzCurrent === WZ_TOTAL - 1) wzBuildReview();
    if (wzCurrent === WZ_TOTAL) {
        const form = document.getElementById('wizard-form');
        if (form) form.submit();
        return;
    }
    wzCurrent++;
    wzUpdateUI();
}
function wzGoBack() { if (wzCurrent > 0) { wzCurrent--; wzUpdateUI(); } }

function wzUpdateUI() {
    for (let i = 0; i <= 5; i++) {
        const p = document.getElementById('wz-step-' + i);
        if (p) p.classList.toggle('active', i === wzCurrent);
    }
    document.querySelectorAll('.wz-step-dot').forEach(d => {
        d.classList.remove('active', 'done');
        const si = parseInt(d.dataset.step);
        if (si < wzCurrent) d.classList.add('done');
        if (si === wzCurrent) d.classList.add('active');
        const num = d.querySelector('.wz-num');
        if (!num) return; // guard: skip if inner element missing
        if (d.classList.contains('done')) num.innerHTML = '<i class="ti ti-check" style="font-size:12px" aria-hidden="true"></i>';
        else num.textContent = si + 1;
    });
    const back    = document.getElementById('wz-btn-back');
    const next    = document.getElementById('wz-btn-next');
    const counter = document.getElementById('wz-counter');
    if (back)    back.style.display = wzCurrent > 0 ? 'inline-flex' : 'none';
    if (counter) counter.textContent = wzCurrent === WZ_TOTAL ? 'Review your entries' : 'Step ' + (wzCurrent + 1) + ' of ' + (WZ_TOTAL);
    if (next)    next.innerHTML = wzCurrent === WZ_TOTAL
        ? 'Save <i class="ti ti-device-floppy" style="font-size:13px" aria-hidden="true"></i>'
        : 'Next <i class="ti ti-arrow-right" style="font-size:13px" aria-hidden="true"></i>';
}

wzUpdateUI();

function wzShowWarning(msg) {
    const msgEl   = document.getElementById('wz-warning-msg');
    const overlay = document.getElementById('wz-warning-overlay');
    if (msgEl)   msgEl.textContent = msg;
    if (overlay) overlay.classList.add('show');
}

function wzCloseWarning() {
    const overlay = document.getElementById('wz-warning-overlay');
    if (overlay) overlay.classList.remove('show');
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

function syncPwdType() {
    const pwdSelect = document.getElementById('f-pwd');
    const pwdTypeInput = document.getElementById('f-pwdtype');
    if (!pwdSelect || !pwdTypeInput) return;
    if (pwdSelect.value === 'No') {
        pwdTypeInput.value = 'N/A';
        pwdTypeInput.disabled = true;
        pwdTypeInput.classList.add('wz-disabled-fill');
    } else if (pwdSelect.value === 'Yes') {
        pwdTypeInput.value = '';
        pwdTypeInput.disabled = false;
        pwdTypeInput.classList.remove('wz-disabled-fill');
    } else {
        pwdTypeInput.value = '';
        pwdTypeInput.disabled = false;
        pwdTypeInput.classList.remove('wz-disabled-fill');
    }
}

    // Names: letters, spaces, periods, apostrophes and hyphens only
    document.querySelectorAll('.alpha-only').forEach(function (el) {
        el.addEventListener('input', function () {
            el.value = el.value.replace(/[^A-Za-z .,'-]/g, '');
        });
    });

    // TIN: digits only, max 9
    var tin = document.getElementById('f-tin');
    if (tin) {
        tin.addEventListener('input', function () {
            tin.value = tin.value.replace(/\D/g, '').slice(0, 9);
        });
    }

    var shs = document.getElementById('f-shs');
    if (shs) shs.addEventListener('change', syncChecklist);
    var nt = document.getElementById('f-nonteaching');
    if (nt) nt.addEventListener('change', syncFinalDeliberation);

    var wzForm = document.getElementById('wizard-form');
    if (wzForm) {
        wzForm.addEventListener('input', syncReadonly);
        wzForm.addEventListener('change', syncReadonly);
    }

    var estatusSelect = document.getElementById('f-estatus');
    if (estatusSelect) {
        estatusSelect.addEventListener('change', syncDateFieldsByStatus);
    }

    var wzWarningOverlay = document.getElementById('wz-warning-overlay');
    if (wzWarningOverlay) {
        wzWarningOverlay.addEventListener('click', function (e) {
            if (e.target === wzWarningOverlay) wzCloseWarning();
        });
    }

    syncChecklist();
    syncFinalDeliberation();
    syncReadonly();
    syncDateFieldsByStatus();

    var plantillaSearchUrl = '{{ route('appointments.plantilla.search') }}';

    function setupAutocomplete(config) {
        const input = document.getElementById(config.inputId);
        const dropdown = document.getElementById(config.dropdownId);
        if (!input || !dropdown) return;

        const fillRelated = config.fillRelated !== false;
        const showSubtitle = config.showSubtitle !== false;

        let debounce;

        input.addEventListener('input', function () {
            const term = input.value.trim();
            clearTimeout(debounce);

            if (term.length < 2) {
                dropdown.style.display = 'none';
                dropdown.innerHTML = '';
                return;
            }

            debounce = setTimeout(function () {
                fetch(plantillaSearchUrl + '?q=' + encodeURIComponent(term) + '&field=' + config.field, { credentials: 'same-origin' })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (!data.length) {
                            dropdown.style.display = 'none';
                            dropdown.innerHTML = '';
                            return;
                        }

                        dropdown.innerHTML = data.map(function (item) {
                            const main = item[config.field] || '—';
                            const sub = showSubtitle
                                ? (config.subtitleFields || ['position', 'school_name', 'city_municipality']).map(function (f) { return item[f]; }).filter(Boolean).join(' — ') || '—'
                                : '';
                            return '<div class="plantilla-item" data-data="' + (item.data || '') + '" data-position="' + (item.position || '') + '" data-school_name="' + (item.school_name || '') + '" data-city_municipality="' + (item.city_municipality || '') + '">' +
                                '<div class="pi-main">' + main + '</div>' +
                                (showSubtitle ? '<div class="pi-sub">' + sub + '</div>' : '') +
                                '</div>';
                        }).join('');

                        dropdown.style.display = 'block';

                        dropdown.querySelectorAll('.plantilla-item').forEach(function (el) {
                            el.addEventListener('click', function () {
                                const pos = document.getElementById('f-pos');
                                const school = document.getElementById('f-school-new');
                                const district = document.getElementById('f-school');
                                const plantilla = document.getElementById('f-plantilla-item');

                                if (fillRelated) {
                                    const targets = config.fillTargets || ['pos', 'school', 'district', 'plantilla'];
                                    if (targets.includes('pos') && config.inputId !== 'f-pos' && pos && el.dataset.position) pos.value = el.dataset.position;
                                    if (targets.includes('school') && config.inputId !== 'f-school-new' && school && el.dataset.school_name) school.value = el.dataset.school_name;
                                    if (targets.includes('district') && config.inputId !== 'f-school' && district && el.dataset.city_municipality) district.value = el.dataset.city_municipality;
                                    if (targets.includes('plantilla') && config.inputId !== 'f-plantilla-item' && plantilla && el.dataset.item) plantilla.value = el.dataset.item;
                                }

                                 input.value = el.dataset[config.field] || '';
                                 dropdown.style.display = 'none';
                                 dropdown.innerHTML = '';

                                  if (config.inputId === 'f-pos') {
                                      const sg = positionSalaryMap[input.value.trim().toUpperCase()];
                                      if (sg !== undefined && sgSelect) {
                                          sgSelect.value = sg;
                                          sgSelect.classList.add('sg-auto-filled');
                                          sgSelect.disabled = true;
                                          sgSelect.dispatchEvent(new Event('change', { bubbles: true }));
                                      } else if (sgSelect) {
                                          sgSelect.disabled = false;
                                          sgSelect.classList.remove('sg-auto-filled');
                                      }
                                  }

                                 syncReadonly();
                            });
                        });
                    })
                    .catch(function () {
                        dropdown.style.display = 'none';
                        dropdown.innerHTML = '';
                    });
            }, 300);
        });

        // Mark this input so the single delegated close-handler can find its dropdown.
        input.dataset.acDropdown = config.dropdownId;
    }

    var sgSelect, stepSelect, salWords, salNums;

    var positionSalaryMap = {
        'ACCOUNTANT I': 12,
        'ACCOUNTANT III': 19,
        'ADMINISTRATIVE AIDE I': 1,
        'ADMINISTRATIVE AIDE III': 3,
        'ADMINISTRATIVE AIDE IV': 4,
        'ADMINISTRATIVE AIDE VI': 6,
        'ADMINISTRATIVE ASSISTANT I': 7,
        'ADMINISTRATIVE ASSISTANT II': 8,
        'ADMINISTRATIVE ASSISTANT III': 9,
        'ADMINISTRATIVE OFFICER I': 10,
        'ADMINISTRATIVE OFFICER II': 11,
        'ADMINISTRATIVE OFFICER IV': 15,
        'ADMINISTRATIVE OFFICER V': 18,
        'ASSISTANT SCHOOL PRINCIPAL II': 19,
        'ASSISTANT SCHOOL PRINCIPAL III': 20,
        'ASSISTANT SCHOOLS DIVISION SUPERINTENDENT': 25,
        'ATTORNEY III': 21,
        'CHIEF EDUCATION SUPERVISOR': 24,
        'DENTAL AIDE': 4,
        'DENTIST II': 17,
        'EDUCATION PROGRAM SPECIALIST II': 16,
        'EDUCATION PROGRAM SUPERVISOR': 22,
        'ENGINEER III': 19,
        'FARM WORKER I': 2,
        'GUIDANCE COORDINATOR III': 13,
        'GUIDANCE COUNSELOR I': 11,
        'GUIDANCE COUNSELOR II': 12,
        'GUIDANCE COUNSELOR III': 13,
        'HANDICRAFT WORKER II': 5,
        'HEAD TEACHER I': 14,
        'HEAD TEACHER II': 15,
        'HEAD TEACHER III': 16,
        'HEAD TEACHER IV': 17,
        'HEAD TEACHER VI': 19,
        'INFORMATION TECHNOLOGY OFFICER I': 19,
        'LABORATORY TECHNICIAN I': 6,
        'LEGAL ASSISTANT I': 10,
        'LIBRARIAN II': 15,
        'MASTER TEACHER I': 18,
        'MASTER TEACHER II': 19,
        'MEDICAL OFFICER II': 18,
        'MEDICAL OFFICER III': 21,
        'NURSE II': 16,
        'PLANNING OFFICER III': 18,
        'PROJECT DEVELOPMENT OFFICER I': 11,
        'PROJECT DEVELOPMENT OFFICER II': 15,
        'PUBLIC SCHOOLS DISTRICT SUPERVISOR': 22,
        'REGISTRAR I': 11,
        'SCHOOL LIBRARIAN I': 11,
        'SCHOOL LIBRARIAN II': 15,
        'SCHOOL LIBRARIAN III': 18,
        'SCHOOL PRINCIPAL I': 19,
        'SCHOOL PRINCIPAL II': 20,
        'SCHOOL PRINCIPAL III': 21,
        'SCHOOL PRINCIPAL IV': 22,
        'SCHOOLS DIVISION SUPERINTENDENT': 26,
        'SECURITY GUARD I': 3,
        'SECURITY GUARD II': 5,
        'SENIOR EDUCATION PROGRAM SPECIALIST': 19,
        'SPECIAL EDUCATION TEACHER I': 14,
        'SPECIAL EDUCATION TEACHER II': 15,
        'SPECIAL EDUCATION TEACHER III': 16,
        'SPECIAL SCIENCE TEACHER I': 13,
        'TEACHER I': 11,
        'TEACHER II': 12,
        'TEACHER III': 13,
        'TEACHER IV': 14,
        'TEACHER V': 15,
        'WATCHMAN I': 2,
    };

    // Single delegated listener to close autocomplete dropdowns on outside click.
    // Uses the same window flag as wizard-modal so only one handler is ever
    // registered across the entire app, regardless of navigation order.
    if (!window.__hrAutocompleteClose) {
        window.__hrAutocompleteClose = true;
        document.addEventListener('click', function (e) {
            document.querySelectorAll('[data-ac-dropdown]').forEach(function (inp) {
                if (inp.contains(e.target)) return;
                const ddId = inp.dataset.acDropdown;
                if (!ddId) return;
                const dd = document.getElementById(ddId);
                if (dd && !dd.contains(e.target)) dd.style.display = 'none';
            });
        });
    }

    function initAppointmentForm() {
        // PAGE GUARD: only run when the create wizard form is inside main content.
        // On the index page the same IDs live inside #hr-modals (wizard-modal),
        // so we check that #wizard-form is a descendant of main.content, not a modal.
        const mainEl = document.querySelector('main.content') || document.querySelector('main');
        if (!mainEl || !mainEl.querySelector('#wizard-form')) return;

        setupAutocomplete({ inputId: 'f-pos', dropdownId: 'position-dropdown', field: 'position', fillRelated: false, showSubtitle: false });
        setupAutocomplete({ inputId: 'f-school', dropdownId: 'district-dropdown', field: 'city_municipality', fillTargets: ['school'], subtitleFields: ['school_name', 'city_municipality'] });
        setupAutocomplete({ inputId: 'f-school-new', dropdownId: 'school-dropdown', field: 'school_name', fillTargets: ['district'], subtitleFields: ['school_name', 'city_municipality'] });
        setupAutocomplete({ inputId: 'f-plantilla-item', dropdownId: 'plantilla-dropdown', field: 'data', fillRelated: false, showSubtitle: false });

        const salaryUrl = '{{ route('appointments.salary') }}';
        sgSelect = document.getElementById('f-sg');
        stepSelect = document.getElementById('f-step');
        salWords = document.getElementById('f-salwords');
        salNums = document.getElementById('f-salnums');

        if (sgSelect) {
            sgSelect.addEventListener('change', function () {
                sgSelect.classList.remove('sg-auto-filled');
            });
        }

        const posInput = document.getElementById('f-pos');
        if (posInput) {
            posInput.addEventListener('input', function () {
                if (!this.value.trim()) {
                    if (sgSelect) {
                        sgSelect.value = '';
                        sgSelect.classList.remove('sg-auto-filled');
                        sgSelect.disabled = false;
                    }
                    if (stepSelect) {
                        stepSelect.value = '';
                    }
                    if (salWords) {
                        salWords.value = '';
                    }
                    if (salNums) {
                        salNums.value = '';
                    }
                } else if (sgSelect && positionSalaryMap[this.value.trim().toUpperCase()] === undefined) {
                    sgSelect.disabled = false;
                    sgSelect.classList.remove('sg-auto-filled');
                }
            });

            posInput.addEventListener('blur', function () {
                if (!this.value.trim() || !sgSelect) return;
                const sg = positionSalaryMap[this.value.trim().toUpperCase()];
                if (sg !== undefined) {
                    sgSelect.value = sg;
                    sgSelect.classList.add('sg-auto-filled');
                    sgSelect.disabled = true;
                    sgSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }

        function updateSalary() {
            if (!sgSelect || !stepSelect || !salWords || !salNums) return;

            const grade = sgSelect.value;
            const step = stepSelect.value;

            if (grade && step) {
                fetch(salaryUrl + '?grade=' + encodeURIComponent(grade) + '&step=' + encodeURIComponent(step), { cache: 'no-store' })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.amount) {
                            salNums.value = data.amount;
                            salWords.value = data.words;
                        } else {
                            salNums.value = '';
                            salWords.value = '';
                        }
                    })
                    .catch(function () {
                        salNums.value = '';
                        salWords.value = '';
                    });
            } else {
                salNums.value = '';
                salWords.value = '';
            }
        }

        if (sgSelect && stepSelect) {
            sgSelect.addEventListener('change', updateSalary);
            stepSelect.addEventListener('change', updateSalary);
        }

        const estatusSelect = document.getElementById('f-estatus');
        if (estatusSelect) {
            estatusSelect.addEventListener('change', function () {
                syncDateFieldsByStatus();
                syncReadonly();
            });
        }

        const shs = document.getElementById('f-shs');
        if (shs) shs.addEventListener('change', syncChecklist);
        const nt = document.getElementById('f-nonteaching');
        if (nt) nt.addEventListener('change', syncFinalDeliberation);

        const wzForm = document.getElementById('wizard-form');
        if (wzForm) {
            wzForm.addEventListener('input', syncReadonly);
            wzForm.addEventListener('change', syncReadonly);
        }

        if (estatusSelect) {
            estatusSelect.addEventListener('change', syncDateFieldsByStatus);
        }

        const pwdSelect = document.getElementById('f-pwd');
        if (pwdSelect) {
            pwdSelect.addEventListener('change', function () {
                syncPwdType();
                syncReadonly();
            });
        }

        const wzWarningOverlay = document.getElementById('wz-warning-overlay');
        if (wzWarningOverlay) {
            wzWarningOverlay.addEventListener('click', function (e) {
                if (e.target === wzWarningOverlay) wzCloseWarning();
            });
        }

        syncChecklist();
        syncFinalDeliberation();
        syncReadonly();
        syncDateFieldsByStatus();
        syncPwdType();
    }

    // Listener deduplication: remove any previously registered handler from an
    // earlier AJAX navigation to this page before registering a fresh one.
    // This prevents the same callback accumulating on repeated visits.
    if (window.__hrCreatePageLoad) {
        document.removeEventListener('hr:page:load', window.__hrCreatePageLoad);
    }
    window.__hrCreatePageLoad = initAppointmentForm;

    document.addEventListener('DOMContentLoaded', initAppointmentForm);
    document.addEventListener('hr:page:load', initAppointmentForm);
    if (document.readyState !== 'loading') {
        initAppointmentForm();
    }

    @if ($errors->any())
        wzShowWarning(@json($errors->first()));
    @endif
</script>
@endpush