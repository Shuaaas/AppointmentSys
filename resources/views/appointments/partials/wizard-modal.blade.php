<div class="overlay" id="overlay-wizard">
    <div class="modal wizard-modal">
        <div class="modal-head">
            <span class="modal-title" id="wizard-modal-title">Add new appointment</span>
            <button type="button" class="modal-close" onclick="wzConfirmClose()" aria-label="Close">&times;</button>
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
                    <div class="wz-step-dot" data-step="4"><div class="wz-dot">5</div><span class="wz-dot-label">Monitoring Data</span></div>
                    <div class="wz-step-dot" data-step="5"><div class="wz-dot">6</div><span class="wz-dot-label">REVIEW</span></div>
                </div>

                {{-- STEP 1: Appointment --}}
                <div class="step-panel active" id="wz-step-0">
                    <div class="wz-section-head">
                        <div class="wz-icon"><i class="ti ti-file-text" aria-hidden="true"></i></div>
                        <div><div class="wz-section-title">Appointment</div><div class="wz-section-sub">Core appointment details</div></div>
                    </div>
                    <div class="wz-grid cols3">
                        <div class="wz-field"><label>Transaction Number</label><input type="text" name="transaction_number" id="f-tn" placeholder="e.g. TN-2026-0001"></div>
                        <div class="wz-field"><label>Date Received by Records</label><input type="date" name="date_received_records" id="f-drec"></div>
                        <div class="wz-field"><label>Date Received by HR</label><input type="date" name="date_received_hr" id="f-dhr"></div>
                        <div class="wz-field"><label>Employee last name *</label><input type="text" name="last_name" id="f-last" placeholder="e.g. Dela Cruz" required></div>
                        <div class="wz-field"><label>Employee first name *</label><input type="text" name="first_name" id="f-first" placeholder="e.g. Maria" required></div>
                        <div class="wz-field"><label>Middle name</label><input type="text" name="middle_name" id="f-middle" placeholder="Optional"></div>

                        <div class="wz-field"><label>Extension name</label><input type="text" name="extension_name" id="f-ext" placeholder="Jr., Sr., III"></div>
                        <div class="wz-field span2" style="position:relative"><label>Position *</label><input type="text" name="position_title" id="f-pos" placeholder="e.g. Teacher III" required autocomplete="off"><div id="position-dropdown" class="plantilla-dropdown" style="display:none"></div></div>

                            <div class="wz-field-pair">
                            <div class="wz-field"><label>Salary grade</label>
                                <select name="salary_grade" id="f-sg">
                                    <option value="">Select grade</option>
                                    @for ($i = 1; $i <= 30; $i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                                </select>
                            </div>
                            <div class="wz-field"><label>Step</label>
                                <select name="salary_grade_step" id="f-step">
                                    <option value="">Select step</option>
                                    @for ($i = 1; $i <= 8; $i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                                </select>
                            </div>
                        </div>
                        <div class="wz-field"><label>Employment status *</label>
                            <select name="employee_status" id="f-estatus" required>
                                <option value="">Select</option><option>Permanent</option><option>Substitute</option><option>Provisional</option>
                            </select>
                        </div>
                        <div class="wz-field" style="position:relative"><label>District</label><input type="text" name="school_district" id="f-school" placeholder="e.g. Batangas NHS" autocomplete="off"><div id="district-dropdown" class="plantilla-dropdown" style="display:none"></div></div>

                        <div class="wz-field" style="position:relative"><label>School</label><input type="text" name="school" id="f-school-new" placeholder="e.g. Batangas National High School" autocomplete="off"><div id="school-dropdown" class="plantilla-dropdown" style="display:none"></div></div>
                        <div class="wz-field" style="position:relative"><label>Plantilla number</label><input type="text" name="plantilla_item_number" id="f-plantilla-item" placeholder="e.g. OSEC-DECSB-T3-123456" autocomplete="off"><div id="plantilla-dropdown" class="plantilla-dropdown" style="display:none"></div></div>
                        <div class="wz-field"><label>Page number</label><input type="text" name="plantilla_page_number" id="f-plantilla-page" placeholder="e.g. 12"></div>

                        <div class="wz-field span2"><label>Salary in words (₱)</label><input type="text" name="compensation_words" id="f-salwords" placeholder="e.g. Twenty-five thousand four hundred thirty-nine" readonly></div>
                        <div class="wz-field"><label>Salary in numbers (₱)</label><input type="text" name="compensation_numbers" id="f-salnums" placeholder="e.g. 25439.00" readonly></div>

                        <div class="wz-field"><label>Nature of Appointment *</label>
                            <select name="nature_of_appointment" id="f-nature" required>
                                <option value="">Select</option><option>Original</option><option>Promotion</option><option>Demotion</option><option>Transfer</option><option>Reclassification</option><option>Reemployment</option><option>Reappointment</option>
                            </select>
                        </div>
                        <div class="wz-field"><label>Incumbent</label><input type="text" name="incumbent" id="f-incumbent" placeholder="Full name"></div>
                        <div class="wz-field"><label>Reason of Incumbent</label><input type="text" name="natural_vacancy" id="f-natural" placeholder="e.g. Transferred, Promotion,..."></div>

                        <div class="wz-field"><label>Date of signing</label><input type="date" name="date_of_signing" id="f-dosign"></div>

                        <div class="wz-date-group" data-status-group="publication">
                            <div class="wz-field"><label>Publication Date (FROM)</label><input type="date" name="publication_date_from" id="f-pubdate-from"></div>
                            <div class="wz-na-field" style="display:none"><label>Publication Date (FROM)</label><input type="text" class="wz-na-input" value="Not Applicable" disabled tabindex="-1"></div>
                        </div>

                        <div class="wz-date-group" data-status-group="publication">
                            <div class="wz-field"><label>Publication Date (TO)</label><input type="date" name="publication_date_to" id="f-pubdate-to"></div>
                            <div class="wz-na-field" style="display:none"><label>Publication Date (TO)</label><input type="text" class="wz-na-input" value="Not Applicable" disabled tabindex="-1"></div>
                        </div>

                        <div class="wz-date-group" data-status-group="assessment">
                            <div class="wz-field"><label>Assessment Date</label><input type="date" name="assessment_date" id="f-assessment"></div>
                            <div class="wz-na-field" style="display:none"><label>Assessment Date</label><input type="text" class="wz-na-input" value="Not Applicable" disabled tabindex="-1"></div>
                        </div>

                        <div class="wz-date-group" data-status-group="deliberation">
                            <div class="wz-field"><label>Deliberation Date</label><input type="date" name="deliberation_date" id="f-deliberation"></div>
                            <div class="wz-na-field" style="display:none"><label>Deliberation Date</label><input type="text" class="wz-na-input" value="Not Applicable" disabled tabindex="-1"></div>
                        </div>
                        <div class="wz-field"><label>TIN</label><input type="text" name="tin" id="f-tin" placeholder="12 digits" maxlength="15"></div>
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
                                <select name="senior_high_strand" id="f-strand" placeholder="Select strand">
                                    <option value="">Select strand</option>
                                    <option value="ABM">Academic Track - ABM</option>
                                    <option value="HUMSS">Academic Track HUMSS</option>
                                    <option value="STEM">Academic Track - STEM</option>
                                    <option value="SHS - TVL Track">SHS - TVL Track</option>
                                    <option value="SHS - Sports Track">SHS - Sports Track</option>
                                </select>
                            </div>
                            <div class="wz-field" id="teaching-level-field" style="display:none"><label>Teaching Level</label>
                                <select name="teaching_level" id="f-teaching-level">
                                    <option value="">Select level</option>
                                    <option value="Elementary">Elementary</option>
                                    <option value="Secondary">Secondary</option>
                                    <option value="Not Applicable">Not Applicable</option>
                                </select>
                            </div>
                            <div class="wz-field"><label>Eligibility</label>
                                <select name="eligibility_type" id="f-elig">
                                    <option value="">Select</option><option value="LET">LET</option><option value="PBET">PBET</option><option value="CSP">CSP</option><option value="CSSP">CSSP</option>
                                </select>
                            </div>
                            <div class="wz-field"><label>Date of Validity of Eligibility</label><input type="date" name="eligibility_validity" id="f-eligvalid"></div>
                            <div class="wz-field"><label>First time used of Eligibility?</label>
                                <select name="eligibility_first_used" id="f-eligfirst">
                                    <option value="">Select</option><option value="Yes">Yes</option><option value="No">No</option>
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
                        <div class="wz-field"><label>Nature of Appointment</label><input type="text" class="wz-readonly" id="rx-rai-nature" readonly tabindex="-1"></div>
                        <div class="wz-field" id="rai-sub-from-field" style="display:none"><label>Substitute (FROM)</label><input type="date" name="substitute_from" id="rx-rai-sub-from"></div>
                        <div class="wz-field" id="rai-sub-to-field" style="display:none"><label>Substitute (TO)</label><input type="date" name="substitute_to" id="rx-rai-sub-to"></div>
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
                        <div class="wz-field">
                            <label>Prepared By</label>
                            <select name="prepared_by" id="f-prepared-by" onchange="if(this.value==='OTHERS'){openOthersModal('f-prepared-by')}">
                                <option value="">Select</option>
                                <option value="MIKA C. TRINIDAD">MIKA C. TRINIDAD</option>
                                <option value="ANGELICA R. CABRAL">ANGELICA R. CABRAL</option>
                                <option value="DIVINA GRACIA E. COSTELO">DIVINA GRACIA E. COSTELO</option>
                                <option value="OTHERS">OTHERS</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- STEP 5: Monitoring Data --}}
                <div class="step-panel" id="wz-step-4">
                    <div class="wz-section-head">
                        <div class="wz-icon"><i class="ti ti-user-check" aria-hidden="true"></i></div>
                        <div><div class="wz-section-title">Monitoring Data</div><div class="wz-section-sub">Personal and demographic information</div></div>
                    </div>
                    <div class="wz-grid">
                        <div class="wz-field"><label>Date of Last Promotion</label><input type="date" name="date_last_promotion" id="f-dlp"></div>
                        <div class="wz-field span2" style="position:relative"><label>Position From <span class="req">*</span></label><input type="text" name="position_from" id="f-pfrom" placeholder="e.g. Teacher I" required autocomplete="off"><div id="position-from-dropdown" class="plantilla-dropdown" style="display:none"></div></div>
                        <div class="wz-field"><label>Position Level <span class="req">*</span></label>
                            <select name="position_level" id="f-poslevel" required>
                                <option value="">Select</option><option value="First Level">1ST</option><option value="Second Level">2ND</option><option value="Third Level">3RD</option>
                            </select>
                        </div>
                        <div class="wz-field"><label>Sex <span class="req">*</span></label>
                            <select name="sex" id="f-sex" required>
                                <option value="">Select</option><option value="Male">Male</option><option value="Female">Female</option><option value="Prefer not to say">Prefer not to say</option>
                            </select>
                        </div>
                        <div class="wz-field"><label>Date of Birth <span class="req">*</span></label><input type="date" name="date_of_birth" id="f-dob" required></div>
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
                        <button type="button" class="btn btn-secondary" id="wz-btn-back" style="display:none" onclick="wzGoBack()">
                            <i class="ti ti-arrow-left" class="icon-sm" aria-hidden="true"></i> Back
                        </button>
                        <button type="button" class="btn btn-blue" id="wz-btn-next" onclick="wzGoNext()">
                            Next <i class="ti ti-arrow-right" class="icon-sm" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="overlay" id="wz-close-confirm">
    <div class="modal" class="modal--narrow">
        <div class="modal-head">
            <span class="modal-title">Cancel editing?</span>
            <button type="button" class="modal-close" onclick="wzCancelClose()" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="confirm-icon"><i class="ti ti-alert-triangle" class="confirm-icon-red" aria-hidden="true"></i></div>
            <p class="confirm-msg">Are you sure you want to cancel? All unsaved changes will be lost.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="wzCancelClose()">No, continue editing</button>
            <button type="button" class="btn btn-danger" onclick="wzDoClose()">Yes, cancel</button>
        </div>
    </div>
</div>

<div class="overlay" id="wz-warning-overlay" class="modal-zindex-high">
    <div class="modal" class="modal--narrow">
        <div class="modal-head">
            <span class="modal-title">Required fields missing</span>
            <button type="button" class="modal-close" onclick="wzCloseWarning()" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="confirm-icon"><i class="ti ti-alert-triangle" class="confirm-icon-red" aria-hidden="true"></i></div>
            <p class="confirm-msg" id="wz-warning-msg"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-blue" onclick="wzCloseWarning()">OK</button>
        </div>
    </div>
</div>

<script>
/* Blade-injected URL vars — must stay inline because they use route() helpers */
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