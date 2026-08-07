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
                            <div class="wz-field"><label>Transaction Number</label><input type="text" name="transaction_number" id="f-tn" placeholder="e.g. TN-2026-0001" value="{{ old('transaction_number') }}"></div>
                            <div class="wz-field"><label>Date Received by Records</label><input type="date" name="date_received_records" id="f-drec" value="{{ old('date_received_records') }}"></div>
                            <div class="wz-field"><label>Date Received by HR</label><input type="date" name="date_received_hr" id="f-dhr" value="{{ old('date_received_hr') }}"></div>
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

                            <div class="wz-field span2"><label>Salary in words (₱)</label><input type="text" name="compensation_words" id="f-salwords" placeholder="e.g. Twenty-five thousand four hundred thirty-nine" value="{{ old('compensation_words') }}" readonly></div>
                            <div class="wz-field"><label>Salary in numbers (₱)</label><input type="text" name="compensation_numbers" id="f-salnums" placeholder="e.g. 25439.00" value="{{ old('compensation_numbers') }}" readonly></div>

                            <div class="wz-field"><label>Nature of Appointment <span class="req">*</span></label>
                                <select name="nature_of_appointment" id="f-nature" required>
                                    <option value="">Select</option><option value="Original" {{ old('nature_of_appointment') === 'Original' ? 'selected' : '' }}>Original</option><option value="Promotion" {{ old('nature_of_appointment') === 'Promotion' ? 'selected' : '' }}>Promotion</option><option value="Demotion" {{ old('nature_of_appointment') === 'Demotion' ? 'selected' : '' }}>Demotion</option><option value="Transfer" {{ old('nature_of_appointment') === 'Transfer' ? 'selected' : '' }}>Transfer</option><option value="Reclassification" {{ old('nature_of_appointment') === 'Reclassification' ? 'selected' : '' }}>Reclassification</option><option value="Reemployment" {{ old('nature_of_appointment') === 'Reemployment' ? 'selected' : '' }}>Reemployment</option><option value="Reappointment" {{ old('nature_of_appointment') === 'Reappointment' ? 'selected' : '' }}>Reappointment</option>
                                </select>
                            </div>

                            <div class="wz-field"><label>Previous Incumbent</label><input type="text" name="incumbent" id="f-incumbent" placeholder="Full name" value="{{ old('incumbent') }}"></div>
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
                            <div class="wz-field"><label>TIN</label><input type="text" name="tin" id="f-tin" placeholder="12 digits" value="{{ old('tin') }}" maxlength="15"></div>
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
                                    <option value="ABM" {{ old('senior_high_strand') === 'ABM' ? 'selected' : '' }}>Academic Track - ABM</option>
                                    <option value="HUMSS" {{ old('senior_high_strand') === 'HUMSS' ? 'selected' : '' }}>Academic Track - HUMSS</option>
                                    <option value="STEM" {{ old('senior_high_strand') === 'STEM' ? 'selected' : '' }}>Academic Track - STEM</option>
                                    <option value="SHS - TVL Track" {{ old('senior_high_strand') === 'SHS - TVL Track' ? 'selected' : '' }}>SHS - TVL Track</option>
                                    <option value="SHS - Sports Track" {{ old('senior_high_strand') === 'SHS - Sports Track' ? 'selected' : '' }}>SHS - Sports Track</option>
                                </select>
                            </div>
                            <div class="wz-field" id="teaching-level-field" style="display:none"><label>Teaching Level</label>
                                <select name="teaching_level" id="f-teaching-level">
                                    <option value="">Select level</option>
                                    <option value="Elementary" {{ old('teaching_level') === 'Elementary' ? 'selected' : '' }}>Elementary</option>
                                    <option value="Secondary" {{ old('teaching_level') === 'Secondary' ? 'selected' : '' }}>Secondary</option>
                                    <option value="Not Applicable" {{ old('teaching_level') === 'Not Applicable' ? 'selected' : '' }}>Not Applicable</option>
                                </select>
                            </div>
                            <div class="wz-field"><label>Eligibility</label>
                                <select name="eligibility_type" id="f-elig">
                                    <option value="">Select</option><option value="LET" {{ old('eligibility_type') === 'LET' ? 'selected' : '' }}>LET</option><option value="PBET" {{ old('eligibility_type') === 'PBET' ? 'selected' : '' }}>PBET</option><option value="CSP" {{ old('eligibility_type') === 'CSP' ? 'selected' : '' }}>CSP</option><option value="CSSP" {{ old('eligibility_type') === 'CSSP' ? 'selected' : '' }}>CSSP</option>
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
                        <div class="wz-field" id="rai-sub-from-field" style="display:none"><label>Substitute (FROM)</label><input type="date" name="substitute_from" id="rx-rai-sub-from"></div>
                        <div class="wz-field" id="rai-sub-to-field" style="display:none"><label>Substitute (TO)</label><input type="date" name="substitute_to" id="rx-rai-sub-to"></div>
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
                            <div class="wz-field">
                                <label>Prepared By</label>
                                <select name="prepared_by" id="f-prepared-by" onchange="if(this.value==='OTHERS'){openOthersModal('f-prepared-by')}">
                                    <option value="">Select</option>
                                    <option value="MIKA C. TRINIDAD" {{ old('prepared_by') === 'MIKA C. TRINIDAD' ? 'selected' : '' }}>MIKA C. TRINIDAD</option>
                                    <option value="ANGELICA R. CABRAL" {{ old('prepared_by') === 'ANGELICA R. CABRAL' ? 'selected' : '' }}>ANGELICA R. CABRAL</option>
                                    <option value="DIVINA GRACIA E. COSTELO" {{ old('prepared_by') === 'DIVINA GRACIA E. COSTELO' ? 'selected' : '' }}>DIVINA GRACIA E. COSTELO</option>
                                    <option value="OTHERS" {{ old('prepared_by') === 'OTHERS' ? 'selected' : '' }}>OTHERS</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- STEP 5: Monitoring Data --}}
                    <div class="step-panel" id="wz-step-4">
                        <div class="wz-grid">
                            <div class="wz-field"><label>Date of Last Promotion</label><input type="date" name="date_last_promotion" id="f-dlp" value="{{ old('date_last_promotion') }}"></div>
                            <div class="wz-field span2" style="position:relative"><label>Position From <span class="req">*</span></label><input type="text" name="position_from" id="f-pfrom" placeholder="e.g. Teacher I" value="{{ old('position_from') }}" required autocomplete="off"><div id="position-from-dropdown" class="plantilla-dropdown" style="display:none"></div></div>
                            <div class="wz-field"><label>Position Level <span class="req">*</span></label>
                                <select name="position_level" id="f-poslevel" required>
                                    <option value="">Select</option><option value="First Level" {{ old('position_level') === 'First Level' ? 'selected' : '' }}>1ST</option><option value="Second Level" {{ old('position_level') === 'Second Level' ? 'selected' : '' }}>2ND</option><option value="Third Level" {{ old('position_level') === 'Third Level' ? 'selected' : '' }}>3RD</option>
                                </select>
                            </div>
                            <div class="wz-field"><label>Sex <span class="req">*</span></label>
                                <select name="sex" id="f-sex" required>
                                    <option value="">Select</option><option value="Male" {{ old('sex') === 'Male' ? 'selected' : '' }}>Male</option><option value="Female" {{ old('sex') === 'Female' ? 'selected' : '' }}>Female</option><option value="Prefer not to say" {{ old('sex') === 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                                </select>
                            </div>
                            <div class="wz-field"><label>Date of Birth <span class="req">*</span></label><input type="date" name="date_of_birth" id="f-dob" value="{{ old('date_of_birth') }}" required></div>
                            <div class="wz-field"><label>PWD?</label>
                                <select name="pwd" id="f-pwd" style="pointer-events:none">
                                    <option value="N/A" selected>N/A</option>
                                </select>
                            </div>
                            <div class="wz-field"><label>Type of Disability</label><input type="text" name="type_of_disability" id="f-pwdtype" value="N/A" readonly class="wz-disabled-fill"></div>
                            <div class="wz-field"><label>Member of IP Group?</label>
                                <select name="ip_group_member" id="f-ip" style="pointer-events:none">
                                    <option value="N/A" selected>N/A</option>
                                </select>
                            </div>
                            <div class="wz-field span2"><label>Ethnicity</label><input type="text" name="ethnicity" id="f-ethnicity" value="Tagalog" readonly class="wz-disabled-fill"></div>
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

@push('modals')
    @include('appointments.partials.others-modal')
@endpush

@push('scripts')
<script>
/* Blade-injected URL vars  must stay inline because they use route() helpers */
window._pamsWizardStoreUrl  = '{{ route('appointments.store') }}';
window._pamsWizardSalaryUrl = '{{ route('appointments.salary') }}';
window._pamsWizardSearchUrl = '{{ route('appointments.plantilla.search') }}';
@if ($errors->any())
window._pamsWizardErrors = @json($errors->first());
@else
window._pamsWizardErrors = null;
@endif
</script>
<script src="{{ asset('js/appointments-wizard.js') }}"></script>
@endpush