/* ============================================================
   PAMS — Wizard Modal JS (for appointments/partials/wizard-modal)
   External vars set inline by Blade template:
     window._pamsWizardStoreUrl     (route appointments.store)
     window._pamsWizardSalaryUrl    (route appointments.salary)
     window._pamsWizardSearchUrl    (route appointments.plantilla.search)
     window._pamsWizardErrors       (null or first error string)
   ============================================================ */

/* ── Position → SG map ── */
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
    'TEACHER VI': 16,
    'TEACHER VII': 17,
    'WATCHMAN I': 2,
};

var wzCurrent = 0;
var WZ_TOTAL = 5;

function openWizard() {
    const form    = document.getElementById('wizard-form');
    const method  = document.getElementById('wizard-method');
    const title   = document.getElementById('wizard-modal-title');
    const overlay = document.getElementById('overlay-wizard');
    if (!form || !method || !title || !overlay) return;

    form.reset();
    form.action = window._pamsWizardStoreUrl || '';
    method.value = 'POST';
    title.textContent = 'Add new appointment';
    wzCurrent = 0;
    wzUpdateUI();
    syncChecklist();
    syncFinalDeliberation();
    syncPwdType();
    syncReadonly();
    syncDateFieldsByStatus();
    overlay.classList.add('show');
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

function wzConfirmClose() {
    const el = document.getElementById('wz-close-confirm');
    if (el) el.classList.add('show');
}

function wzCancelClose() {
    const el = document.getElementById('wz-close-confirm');
    if (el) el.classList.remove('show');
}

function wzDoClose() {
    const confirm  = document.getElementById('wz-close-confirm');
    const overlay  = document.getElementById('overlay-wizard');
    if (confirm) confirm.classList.remove('show');
    if (overlay) overlay.classList.remove('show');
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
    const raiSubFromEl = document.getElementById('rx-rai-sub-from');
    const raiSubToEl = document.getElementById('rx-rai-sub-to');
    const isNonPermanentOrReclass = wg('f-estatus') === 'Substitute' || wg('f-estatus') === 'Provisional' || wg('f-nature') === 'Reclassification';
    if (raiSubFromEl && !raiSubFromEl.value) raiSubFromEl.value = isNonPermanentOrReclass ? (wg('f-pubdate-from') || '') : '';
    if (raiSubToEl && !raiSubToEl.value) raiSubToEl.value = isNonPermanentOrReclass ? (wg('f-pubdate-to') || '') : '';

    set('rx-fd-empname', name);  set('rx-fd-pos', wg('f-pos'));
    set('rx-fd-dosign', wg('f-dosign'));  set('rx-fd-school', wg('f-school-new'));

    set('rx-md-empname', name);  set('rx-md-pos', wg('f-pos'));
    set('rx-md-dlp', wg('f-dlp'));  set('rx-md-pfrom', wg('f-pfrom'));
    set('rx-md-poslevel', wg('f-poslevel'));
    set('rx-md-sex', wg('f-sex'));
    set('rx-md-dob', wg('f-dob'));
    set('rx-md-pwd', 'N/A');
    set('rx-md-pwdtype', 'N/A');
    set('rx-md-ip', 'N/A');
    set('rx-md-ethnicity', 'Tagalog');
}

function syncDateFieldsByStatus() {
    const estatus = document.getElementById('f-estatus');
    const nature = document.getElementById('f-nature');
    const groups = document.querySelectorAll('.wz-date-group');
    const raiSubFrom = document.getElementById('rai-sub-from-field');
    const raiSubTo = document.getElementById('rai-sub-to-field');
    if (!estatus) return;

    const status = estatus.value.trim().toLowerCase();
    const natureVal = nature ? nature.value.trim() : '';
    const isNonPermanent = status === 'substitute' || status === 'provisional' || natureVal === 'Reclassification';

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

    if (raiSubFrom) raiSubFrom.style.display = isNonPermanent ? 'block' : 'none';
    if (raiSubTo) raiSubTo.style.display = isNonPermanent ? 'block' : 'none';
}

function fdResult() {
    const v = document.getElementById('f-nonteaching')?.value;
    return v === 'Yes' ? 'RUBEN E. FALTADO III' : (v === 'No' ? 'ANTONIO P. FAUSTINO JR.' : '—');
}

function wzBuildReview() {
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
            ['Education', wg('f-education')], ['Senior high school?', wg('f-shs')], ['Strand', wg('f-shs') === 'No' ? 'N/A' : wg('f-strand')], ['Teaching Level', wg('f-shs') === 'No' ? wg('f-teaching-level') : 'N/A'],
            ['Eligibility', wg('f-elig')], ['Date of Validity', wg('f-eligvalid')],
            ['First time used?', wg('f-eligfirst')]
        ]},
        { title: 'RAI', rows: [
            ['Employee name', rxv('rx-rai-empname')], ['Position', rxv('rx-rai-pos')],
            ['Plantilla number', rxv('rx-rai-plantilla')], ['Salary grade', rxv('rx-rai-sg')],
            ['Salary number', rxv('rx-rai-salnum')], ['Employment status', rxv('rx-rai-estatus')],
            ['Appointment nature', rxv('rx-rai-nature')],
            ['Substitute (FROM)', rxv('rx-rai-sub-from') || (rxv('rx-rai-estatus') === 'Substitute' || rxv('rx-rai-estatus') === 'Provisional' || rxv('rx-rai-nature') === 'Reclassification' ? 'N/A' : '')],
            ['Substitute (TO)', rxv('rx-rai-sub-to') || (rxv('rx-rai-estatus') === 'Substitute' || rxv('rx-rai-estatus') === 'Provisional' || rxv('rx-rai-nature') === 'Reclassification' ? 'N/A' : '')]
        ]},
        { title: 'Final Deliberation', rows: [
            ['Employee name', rxv('rx-fd-empname')], ['Position', rxv('rx-fd-pos')],
            ['Date of signing', rxv('rx-fd-dosign')], ['School', rxv('rx-fd-school')],
            ['Non teaching?', wg('f-nonteaching')], ['Result', fdResult()],
            ['Prepared By', wg('f-prepared-by')]
        ]},
        { title: 'Monitoring Data', rows: [
            ['Employee name', wzEmployeeName()], ['Position', wg('f-pos')],
            ['Date of Last Promotion', wg('f-dlp')], ['Position From', wg('f-pfrom')],
            ['Position Level', wg('f-poslevel')],
            ['Sex', wg('f-sex')], ['Date of Birth', wg('f-dob')],             ['PWD?', 'N/A'],
            ['Type of Disability', 'N/A'],
            ['Member of IP Group?', 'N/A'], ['Ethnicity', 'Tagalog']
        ]}
    ];
    const reviewEl = document.getElementById('wz-review-content');
    if (!reviewEl) return;
    reviewEl.innerHTML = sections.map(s => `
        <div>
            <div class="review-group-title">${s.title}</div>
            ${s.rows.map(r => `<div class="review-row"><span class="review-key">${r[0]}</span><span class="review-val">${r[1]}</span></div>`).join('')}
        </div>`).join('');
}

function wzGoNext() {
    if (wzCurrent === 0 && (!document.getElementById('f-last').value || !document.getElementById('f-first').value)) {
        wzShowWarning('Please fill in the required fields (Last name, First name) before continuing.');
        return;
    }
    if (wzCurrent === 0 && (!document.getElementById('f-pos').value || !document.getElementById('f-estatus').value || !document.getElementById('f-nature').value)) {
        wzShowWarning('Please fill in the required fields (Position, Employment status, Appointment nature) before continuing.');
        return;
    }
    const validNatures = ['Original', 'Promotion', 'Demotion', 'Transfer', 'Reclassification', 'Reemployment', 'Reappointment'];
    if (wzCurrent === 0 && document.getElementById('f-nature').value && !validNatures.includes(document.getElementById('f-nature').value)) {
        wzShowWarning('Please select a valid nature of appointment before continuing.');
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
    for (let i = 0; i <= 5; i++) {
        const p = document.getElementById('wz-step-' + i);
        if (p) p.classList.toggle('active', i === wzCurrent);
    }
    document.querySelectorAll('.wz-step-dot').forEach(d => {
        d.classList.remove('active', 'done');
        const si = parseInt(d.dataset.step);
        if (si < wzCurrent) d.classList.add('done');
        if (si === wzCurrent) d.classList.add('active');
        const dot = d.querySelector('.wz-dot');
        if (!dot) return;
        if (d.classList.contains('done')) dot.innerHTML = '<i class="ti ti-check icon-sm" aria-hidden="true"></i>';
        else dot.textContent = si + 1;
    });
    const back    = document.getElementById('wz-btn-back');
    const next    = document.getElementById('wz-btn-next');
    const counter = document.getElementById('wz-counter');
    if (back)    back.style.display = wzCurrent > 0 ? 'inline-flex' : 'none';
    if (counter) counter.textContent = wzCurrent === WZ_TOTAL ? 'Review your entries' : 'Step ' + (wzCurrent + 1) + ' of ' + (WZ_TOTAL);
    if (next)    next.innerHTML = wzCurrent === WZ_TOTAL
        ? 'Save <i class="ti ti-device-floppy icon-sm" aria-hidden="true"></i>'
        : 'Next <i class="ti ti-arrow-right icon-sm" aria-hidden="true"></i>';
}

function syncChecklist() {
    const shs = document.getElementById('f-shs');
    const strand = document.getElementById('strand-field');
    const strandSelect = document.getElementById('f-strand');
    const teachingLevel = document.getElementById('teaching-level-field');
    const teachingLevelSelect = document.getElementById('f-teaching-level');
    if (shs && strand && teachingLevel) {
        if (shs.value === 'Yes') {
            strand.style.display = 'block';
            teachingLevel.style.display = 'none';
            if (teachingLevelSelect) teachingLevelSelect.value = '';
        } else if (shs.value === 'No') {
            strand.style.display = 'none';
            teachingLevel.style.display = 'block';
            if (strandSelect) strandSelect.value = 'N/A';
        } else {
            strand.style.display = 'none';
            teachingLevel.style.display = 'none';
            if (strandSelect) strandSelect.value = '';
            if (teachingLevelSelect) teachingLevelSelect.value = '';
        }
    }
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
    const pwdTypeInput = document.getElementById('f-pwdtype');
    if (!pwdTypeInput) return;
    pwdTypeInput.value = 'N/A';
    pwdTypeInput.disabled = true;
    pwdTypeInput.classList.add('wz-disabled-fill');
}

function setupAutocomplete(config) {
    const input = document.getElementById(config.inputId);
    const dropdown = document.getElementById(config.dropdownId);
    if (!input || !dropdown) return;

    const fillRelated = config.fillRelated !== false;
    const showSubtitle = config.showSubtitle !== false;
    const plantillaSearchUrl = window._pamsWizardSearchUrl || '';
    const salaryUrl = window._pamsWizardSalaryUrl || '';

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
                                 const liveSg = document.getElementById('f-sg');
                                 const liveStep = document.getElementById('f-step');
                                 const sg = positionSalaryMap[input.value.trim().toUpperCase()];
                                 if (sg !== undefined && liveSg) {
                                     liveSg.value = sg;
                                     liveSg.classList.add('sg-auto-filled');
                                     if (liveStep && !liveStep.value) {
                                         liveStep.value = '1';
                                     }
                                     liveSg.dispatchEvent(new Event('change', { bubbles: true }));
                                 } else if (liveSg) {
                                     liveSg.classList.remove('sg-auto-filled');
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

    input.dataset.acDropdown = config.dropdownId;
}

// Single delegated listener to close autocomplete dropdowns when clicking outside
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

function updateSalary() {
    const sgSelect   = document.getElementById('f-sg');
    const stepSelect = document.getElementById('f-step');
    const salWords   = document.getElementById('f-salwords');
    const salNums    = document.getElementById('f-salnums');
    const salaryUrl  = window._pamsWizardSalaryUrl || '';

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

function initWizardModal() {
    const wzOverlay = document.getElementById('overlay-wizard');
    if (wzOverlay && !wzOverlay.dataset.closeListenerAdded) {
        wzOverlay.dataset.closeListenerAdded = '1';
        wzOverlay.addEventListener('click', function(e) {
            if (e.target === wzOverlay) {
                e.stopImmediatePropagation();
            }
        }, true);
    }

    const sgSelect   = document.getElementById('f-sg');
    const stepSelect = document.getElementById('f-step');
    const posInput   = document.getElementById('f-pos');

    if (sgSelect) {
        sgSelect.addEventListener('change', function () {
            sgSelect.classList.remove('sg-auto-filled');
            updateSalary();
        });
    }
    if (stepSelect) {
        stepSelect.addEventListener('change', updateSalary);
    }

    if (posInput) {
        posInput.addEventListener('input', function () {
            if (!this.value.trim()) {
                if (sgSelect) { sgSelect.value = ''; sgSelect.classList.remove('sg-auto-filled'); sgSelect.disabled = false; }
                if (stepSelect) stepSelect.value = '';
                if (document.getElementById('f-salwords')) document.getElementById('f-salwords').value = '';
                if (document.getElementById('f-salnums')) document.getElementById('f-salnums').value = '';
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
                if (stepSelect && !stepSelect.value) {
                    stepSelect.value = '1';
                }
                sgSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }

    const wzWarningOverlay = document.getElementById('wz-warning-overlay');
    if (wzWarningOverlay) {
        wzWarningOverlay.addEventListener('click', function (e) {
            if (e.target === wzWarningOverlay) wzCloseWarning();
        });
    }

    const wzForm = document.getElementById('wizard-form');
    if (wzForm) {
        wzForm.addEventListener('input', syncReadonly);
        wzForm.addEventListener('change', syncReadonly);
        wzForm.addEventListener('submit', function () {
            var tin = document.getElementById('f-tin');
            if (tin) tin.value = tin.value.replace(/-/g, '');
        });
    }

    const estatusSelect = document.getElementById('f-estatus');
    if (estatusSelect) {
        estatusSelect.addEventListener('change', function () {
            syncDateFieldsByStatus();
            syncReadonly();
        });
    }

    const natureSelect = document.getElementById('f-nature');
    if (natureSelect) {
        natureSelect.addEventListener('change', function () {
            syncDateFieldsByStatus();
            syncReadonly();
        });
    }

    setupAutocomplete({ inputId: 'f-pos', dropdownId: 'position-dropdown', field: 'position', fillRelated: false, showSubtitle: false });
    setupAutocomplete({ inputId: 'f-pfrom', dropdownId: 'position-from-dropdown', field: 'position', fillRelated: false, showSubtitle: false });
    setupAutocomplete({ inputId: 'f-school', dropdownId: 'district-dropdown', field: 'city_municipality', fillTargets: ['school'], subtitleFields: ['school_name', 'city_municipality'] });
    setupAutocomplete({ inputId: 'f-school-new', dropdownId: 'school-dropdown', field: 'school_name', fillTargets: ['district'], subtitleFields: ['school_name', 'city_municipality'] });
    setupAutocomplete({ inputId: 'f-plantilla-item', dropdownId: 'plantilla-dropdown', field: 'data', fillRelated: false, showSubtitle: false });

    const shs = document.getElementById('f-shs');
    if (shs) shs.addEventListener('change', syncChecklist);
    const nt = document.getElementById('f-nonteaching');
    if (nt) nt.addEventListener('change', syncFinalDeliberation);

    const pwdSelect = document.getElementById('f-pwd');
    if (pwdSelect) {
        pwdSelect.addEventListener('change', function () {
            syncPwdType();
            syncReadonly();
        });
    }

    const tin = document.getElementById('f-tin');
    if (tin) {
        tin.addEventListener('input', function () {
            var digits = this.value.replace(/\D/g, '').slice(0, 12);
            this.value = digits.replace(/(\d{3})(?=\d)/g, '$1-');
        });
    }

    syncChecklist();
    syncFinalDeliberation();
    syncPwdType();
    syncReadonly();
    syncDateFieldsByStatus();

    // Show server-side validation errors (injected by Blade template as window._pamsWizardErrors)
    if (window._pamsWizardErrors) {
        wzShowWarning(window._pamsWizardErrors);
        wzCurrent = 0;
        wzUpdateUI();
        var overlay = document.getElementById('overlay-wizard');
        if (overlay) overlay.classList.add('show');
        window._pamsWizardErrors = null; // prevent re-trigger
    }
}

// Run immediately — the modal HTML is already in the DOM at this point
initWizardModal();
