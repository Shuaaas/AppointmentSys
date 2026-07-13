<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Services\AppointmentFormService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AppointmentController extends Controller
{
    /**
     * List view — defaults to the latest encoded date only.
     * All 4 roles can view (HR, Records, Manager, Admin) — the policy's
     * viewAny() allows this; write actions below are what's actually gated.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Appointment::class);

        $availableDates = Appointment::active()
            ->selectRaw('DATE(encoded_at) as d')
            ->distinct()
            ->orderByDesc('d')
            ->pluck('d');

        $selectedDate = $request->query('date', $availableDates->first());
        $selectedStatus = $request->query('status');
        $selectedNature = $request->query('nature');
        $selectedTab = $request->query('tab', 'needs');

        // Completed records move to the Archive page, so HR and Manager no
        // longer see them in Appointment Data — only active/in-progress remain.
        $visibleStates = ['new', 'active', 'in_progress', 'completed'];
        if ($request->user()->isHr() || $request->user()->isManager()) {
            $visibleStates = ['new', 'active', 'in_progress'];
        }

        $appointmentsQuery = Appointment::whereIn('record_state', $visibleStates)
            ->when($selectedStatus, function ($q, $selectedStatus) {
                if ($selectedStatus === 'active') {
                    $q->whereIn('record_state', ['new', 'active']);
                } elseif ($selectedStatus === 'in_progress') {
                    $q->where('record_state', 'in_progress');
                } elseif ($selectedStatus === 'completed') {
                    $q->where('record_state', 'completed');
                }
            })
            ->when($selectedNature, fn ($q) => $q->where('nature_of_appointment', $selectedNature))
            ->when($selectedDate, fn ($q) => $q->whereDate('encoded_at', $selectedDate))
            ->search($request->query('q'));

        if ($request->user()->isRecords()) {
            $appointmentsQuery = $appointmentsQuery->when($selectedTab === 'needs', fn ($q) => $q->where('record_state', 'in_progress')
                    ->where(function ($query) {
                        $query->whereNull('transaction_number')->orWhere('transaction_number', '');
                    }))
                ->when($selectedTab === 'completed', fn ($q) => $q->where('record_state', 'completed')
                    ->whereNotNull('transaction_number')->where('transaction_number', '<>', ''));
        }

        $appointments = $appointmentsQuery->orderByDesc('encoded_at')->get();

        $needsTNCount = Appointment::whereIn('record_state', $visibleStates)
            ->where(function ($query) {
                $query->whereNull('transaction_number')->orWhere('transaction_number', '');
            })
            ->count();

        $completedCount = Appointment::whereIn('record_state', $visibleStates)
            ->whereNotNull('transaction_number')
            ->where('transaction_number', '<>', '')
            ->count();

        $completedTodayCount = Appointment::whereIn('record_state', $visibleStates)
            ->whereNotNull('transaction_number')
            ->where('transaction_number', '<>', '')
            ->whereDate('updated_at', now())
            ->count();

        $monthlyTotalCount = Appointment::whereIn('record_state', $visibleStates)
            ->whereYear('encoded_at', now()->year)
            ->whereMonth('encoded_at', now()->month)
            ->count();

        return view('appointments.index', [
            'appointments'       => $appointments,
            'availableDates'     => $availableDates,
            'selectedDate'       => $selectedDate,
            'selectedStatus'     => $selectedStatus,
            'selectedNature'     => $selectedNature,
            'selectedTab'        => $selectedTab,
            'search'             => $request->query('q'),
            'needsTNCount'       => $needsTNCount,
            'completedCount'     => $completedCount,
            'completedTodayCount'=> $completedTodayCount,
            'monthlyTotalCount'  => $monthlyTotalCount,
        ]);
    }

    /**
     * Show the create appointment page.
     * HR only.
     */
    public function create(): View
    {
        $this->authorize('create', Appointment::class);

        return view('appointments.create');
    }

    /**
     * Create a new appointment record.
     * Policy: HR + Admin only.
     */
    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Appointment::class);

        $data = $request->validated();
        $data['encoding_personnel'] = $data['encoding_personnel'] ?? auth()->user()->name ?? 'HRMO Offline Admin';
        $data['record_state'] = 'active';

        $appointment = Appointment::create($data);

        return redirect()
            ->route('appointments.index')
            ->with('success', "Appointment for {$appointment->full_name} was saved successfully.");
    }

    /**
     * Bulk soft-delete to History/Trash.
     * Policy: Admin only. Checked directly (not per-model) since this
     * spans an arbitrary set of records in one request.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403, 'Only Admin can delete appointments.');

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:appointments,id'],
        ]);

        $ids = $data['ids'];

        Appointment::whereIn('id', $ids)->update(['record_state' => 'deleted']);
        Appointment::whereIn('id', $ids)->delete();

        return redirect()
            ->route('appointments.index')
            ->with('success', count($ids) > 1
                ? "Selected appointments were moved to History."
                : "Selected appointment was moved to History.");
    }

    public function show(Request $request, Appointment $appointment)
    {
        $this->authorize('view', $appointment);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($appointment);
        }

        return view('appointments.show', compact('appointment'));
    }

    /**
     * Full-record update.
     * Policy: HR + Admin only — Records must use updateTransactionNumber()
     * below instead, which only touches one column.
     */
    public function update(StoreAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        $appointment->update($request->validated());

        return redirect()
            ->route('appointments.index')
            ->with('success', "Appointment for {$appointment->full_name} was updated.");
    }

    /**
     * Records role: the ONLY field they can edit.
     * Policy: Records + Admin. Deliberately validates and writes
     * a single column — no other field can be smuggled into this request.
     */
    public function updateTransactionNumber(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('updateTransactionNumber', $appointment);

        $request->validate([
            'transaction_number' => ['required', 'string', 'max:255'],
        ]);

        $appointment->update([
            'transaction_number' => $request->transaction_number,
            'record_state' => 'completed',
        ]);

        return redirect()
            ->route('appointments.index')
            ->with('tn_saved', $request->transaction_number)
            ->with('tn_name', $appointment->full_name);
    }

    /**
     * Soft delete — moves the record to Trash.
     * Policy: Admin only.
     */
    public function destroy(Appointment $appointment): RedirectResponse
    {
        $this->authorize('delete', $appointment);

        $appointment->update(['record_state' => 'deleted']);
        $appointment->delete();

        return redirect()
            ->route('appointments.index')
            ->with('success', "Appointment for {$appointment->full_name} was moved to History.");
    }

    /**
     * Mark an appointment as concluded — moves it into History.
     * Policy: mapped to "archive" ability — HR + Admin, matching
     * the "HR → archive records" permission.
     */
    public function conclude(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('archive', $appointment);

        $request->validate([
            'conclusion_reason' => ['required', 'string', 'max:255'],
            'date_concluded'    => ['required', 'date'],
        ]);

        $appointment->update([
            'record_state'      => 'concluded',
            'conclusion_reason' => $request->conclusion_reason,
            'date_concluded'    => $request->date_concluded,
        ]);

        return redirect()
            ->route('appointments.index')
            ->with('success', "Appointment for {$appointment->full_name} was moved to History.");
    }

    /**
     * Archive — lists records that have reached the "Completed" status.
     * Policy: viewable by HR, Records, and Manager (route middleware gates access).
     */
    public function archive(): View
    {
        $this->authorize('viewAny', Appointment::class);

        $appointments = Appointment::where('record_state', 'completed')
            ->orderByDesc('updated_at')
            ->get();

        return view('appointments.archive', compact('appointments'));
    }

    /**
     * Trash bin — lists soft-deleted records.
     * Policy: Admin only (kept as locked-down as delete itself).
     */
    public function trash(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);

        $trashed = Appointment::onlyTrashed()->orderByDesc('deleted_at')->get();

        return view('appointments.trash', compact('trashed'));
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $appointment = Appointment::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $appointment);

        $appointment->restore();
        $appointment->update(['record_state' => 'active']);

        return redirect()->back()
            ->with('success', "Appointment for {$appointment->full_name} was restored.");
    }

    public function forceDelete(Request $request, int $id): RedirectResponse
    {
        $appointment = Appointment::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $appointment);

        $appointment->forceDelete();

        return redirect()
            ->route('appointments.trash')
            ->with('success', 'Appointment was permanently deleted.');
    }

    /**
     * Document generation (Word/Excel). Policy: mapped to "print" —
     * HR + Admin only, matching "HR → Print documents."
     */
    public function exportAfa(Appointment $appointment, AppointmentFormService $service)
    {
        $this->authorize('print', $appointment);

        try {
            $appointment->markDownloaded('afa');
            $appointment->evaluateWorkflowState();
        } catch (\Throwable $e) {
            \Log::warning('Unable to record AFA download for appointment ' . $appointment->id . ': ' . $e->getMessage());
        }

        $filePath = $service->generate($appointment);
        $safeName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $appointment->full_name);
        $filename = sprintf('Appointment Form - %s.docx', $safeName ?: $appointment->transaction_number ?? $appointment->id);

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    public function downloadChecklist(Appointment $appointment, AppointmentFormService $service)
    {
        $this->authorize('print', $appointment);

        try {
            $appointment->markDownloaded('checklist');
            $appointment->evaluateWorkflowState();
        } catch (\Throwable $e) {
            \Log::warning('Unable to record Checklist download for appointment ' . $appointment->id . ': ' . $e->getMessage());
        }

        $filePath = $service->generateChecklist($appointment);
        $safeName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $appointment->full_name);
        $filename = sprintf('Checklist - %s.xlsx', $safeName ?: $appointment->transaction_number ?? $appointment->id);

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function downloadRai(Appointment $appointment, AppointmentFormService $service)
    {
        $this->authorize('print', $appointment);

        try {
            $appointment->markDownloaded('rai');
            $appointment->evaluateWorkflowState();
        } catch (\Throwable $e) {
            \Log::warning('Unable to record RAI download for appointment ' . $appointment->id . ': ' . $e->getMessage());
        }

        $filePath = $service->generateRai($appointment);
        $safeName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $appointment->full_name);
        $filename = sprintf('Report on Appointment Issued - %s.xlsx', $safeName ?: $appointment->transaction_number ?? $appointment->id);

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function downloadFinalDeliberation(Appointment $appointment, AppointmentFormService $service)
    {
        $this->authorize('print', $appointment);

        try {
            $appointment->markDownloaded('final');
            $appointment->evaluateWorkflowState();
        } catch (\Throwable $e) {
            \Log::warning('Unable to record Final Deliberation download for appointment ' . $appointment->id . ': ' . $e->getMessage());
        }

        $filePath = $service->generateFinalDeliberation($appointment);
        $safeName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $appointment->full_name);
        $filename = sprintf('Final Deliberation - %s.docx', $safeName ?: $appointment->transaction_number ?? $appointment->id);

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Bulk document generation (ZIP) or fallback CSV export.
     * Policy: mapped to "print" — HR + Admin only, since the primary
     * path here generates official documents, not just a data export.
     * Checked directly (class-level) since this can act on many records.
     */
    public function exportCsv(Request $request, AppointmentFormService $service)
    {
        abort_unless(
            $request->user()->isHr() || $request->user()->isAdmin(),
            403,
            'Only HR and Admin can export or generate appointment documents.'
        );

        $ids = $request->input('ids');

        if (is_array($ids) && count($ids) > 0) {
            $appointments = Appointment::whereIn('id', $ids)->get();

            if ($appointments->isEmpty()) {
                abort(404, 'No appointments found for selected IDs.');
            }

            $outputDirectory = storage_path('app/temp/appointment-forms');
            File::ensureDirectoryExists($outputDirectory);

            $files = [];

            foreach ($appointments as $a) {
                $last = $a->last_name ?? '';
                $first = $a->first_name ?? '';
                $middle = $a->middle_name ?? '';

                $safeLast = preg_replace('/[^A-Za-z0-9_]/', '_', trim(str_replace(' ', '_', $last)));
                $safeFirst = preg_replace('/[^A-Za-z0-9_]/', '_', trim(str_replace(' ', '_', $first)));
                $safeMiddle = $middle ? preg_replace('/[^A-Za-z0-9_]/', '_', trim(str_replace(' ', '_', $middle))) : null;

                $txn = $a->transaction_number ?? $a->id;

                $year = now()->format('Y');
                $month = strtoupper(now()->format('F'));
                $token = substr(bin2hex(random_bytes(3)), 0, 6);

                $nameParts = [$safeLast, $safeFirst];
                if ($safeMiddle) { $nameParts[] = $safeMiddle; }
                $personPart = implode('_', $nameParts);

                $folderName = sprintf('%s-%s-%s-%s-%s.docx', $personPart, $txn, $year, $month, $token);

                try {
                    $formPath = $service->generateWithTemplateFile($a, 'Appointment Form Generated Template.docx');
                    try { $a->markDownloaded('afa'); } catch (\Throwable $e) { \Log::warning('Unable to mark AFA downloaded for ' . $a->id . ': ' . $e->getMessage()); }
                    $files[] = ['path' => $formPath, 'name' => $folderName . '/' . sprintf('%s_Appointment.docx', $personPart)];
                } catch (\Throwable $e) {
                    \Log::error('Failed to generate appointment form for ' . $txn . ': ' . $e->getMessage());
                }

                try {
                    $fdPath = $service->generateFinalDeliberation($a);
                    try { $a->markDownloaded('final'); } catch (\Throwable $e) { \Log::warning('Unable to mark Final downloaded for ' . $a->id . ': ' . $e->getMessage()); }
                    $files[] = ['path' => $fdPath, 'name' => $folderName . '/' . sprintf('%s_FinalDeliberation.docx', $personPart)];
                } catch (\Throwable $e) {
                    \Log::error('Failed to generate final deliberation for ' . $txn . ': ' . $e->getMessage());
                }

                try {
                    $chkPath = $service->generateChecklist($a);
                    try { $a->markDownloaded('checklist'); } catch (\Throwable $e) { \Log::warning('Unable to mark Checklist downloaded for ' . $a->id . ': ' . $e->getMessage()); }
                    $files[] = ['path' => $chkPath, 'name' => $folderName . '/' . sprintf('%s_Checklist.xlsx', $personPart)];
                } catch (\Throwable $e) {
                    \Log::error('Failed to generate checklist for ' . $txn . ': ' . $e->getMessage());
                }

                try {
                    $raiPath = $service->generateRai($a);
                    try { $a->markDownloaded('rai'); } catch (\Throwable $e) { \Log::warning('Unable to mark RAI downloaded for ' . $a->id . ': ' . $e->getMessage()); }
                    $files[] = ['path' => $raiPath, 'name' => $folderName . '/' . sprintf('%s_RAI.xlsx', $personPart)];
                } catch (\Throwable $e) {
                    \Log::error('Failed to generate RAI for ' . $txn . ': ' . $e->getMessage());
                }

                try { $a->evaluateWorkflowState(); } catch (\Throwable $e) { \Log::warning('Unable to evaluate workflow state for appointment ' . $a->id . ': ' . $e->getMessage()); }
            }

            $zipName = 'appointments_' . now()->format('Ymd_His') . '.zip';
            $zipPath = $outputDirectory . DIRECTORY_SEPARATOR . $zipName;

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Unable to create ZIP archive.');
            }

            foreach ($files as $f) {
                $zip->addFile($f['path'], $f['name']);
            }

            $zip->close();

            foreach ($files as $f) {
                try { @unlink($f['path']); } catch (\Throwable $e) { }
            }

            return response()->download($zipPath, $zipName, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);
        }

        $appointments = Appointment::active()->orderByDesc('encoded_at')->get();

        $columns = [
            'Transaction Number', 'Last Name', 'First Name', 'Middle Name',
            'Position Title', 'School/District', 'Nature of Appointment',
            'Employee Status', 'Date Original Appointment', 'Eligibility',
            'Monthly Salary', 'Encoding Personnel', 'Date Encoded',
        ];

        $callback = function () use ($appointments, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            foreach ($appointments as $a) {
                fputcsv($handle, [
                    $a->transaction_number, $a->last_name, $a->first_name, $a->middle_name,
                    $a->position_title, $a->school_district, $a->nature_of_appointment,
                    $a->employee_status, optional($a->date_original_appointment)->format('Y-m-d'),
                    $a->eligibility_type, $a->monthly_salary, $a->encoding_personnel,
                    optional($a->encoded_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        };

        $filename = 'appointments_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}