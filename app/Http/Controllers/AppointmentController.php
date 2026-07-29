<?php

namespace App\Http\Controllers;

use App\Http\Requests\Appointment\BulkDestroyAppointmentRequest;
use App\Http\Requests\Appointment\ConcludeAppointmentRequest;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Requests\Appointment\UpdateTransactionNumberRequest;
use App\Models\Appointment;
use App\Models\User;
use App\Services\Appointment\DocumentExportService;
use App\Services\AppointmentFormService;
use App\Traits\GeneratesSafeFilename;
use App\Traits\TracksDocumentDownload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use ZipArchive;

class AppointmentController extends Controller
{
    use GeneratesSafeFilename, TracksDocumentDownload;

    /**
     * List view — defaults to the latest encoded date only.
     * All 4 roles can view (HR, Records, Manager, Admin) — the policy's
     * viewAny() allows this; write actions below are what's actually gated.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Appointment::class);

        $hrUsers = User::where('role', 'hr')->get();
        $selectedHrUserId = $request->query('hr_user');

        $availableDates = Appointment::query()
            ->when($request->user()->isHr(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->user()->isRecords() || $request->user()->isManager(), fn ($q) => $q->whereNotNull('user_id'))
            ->when($request->user()->isRecords() && $selectedHrUserId, fn ($q) => $q->where('user_id', $selectedHrUserId))
            ->active()
            ->selectRaw('DATE(encoded_at) as d')
            ->distinct()
            ->orderByDesc('d')
            ->pluck('d');

        $selectedDate   = $request->query('date', $availableDates->first());
        $selectedStatus = $request->query('status');
        $selectedNature = $request->query('nature');
        $selectedTab    = $request->query('tab', 'needs');

        $visibleStates = ['new', 'active', 'in_progress', 'completed'];
        if ($request->user()->isHr() || $request->user()->isManager()) {
            $visibleStates = ['new', 'active', 'in_progress'];
        }

        $appointmentsQuery = Appointment::query()
            ->when($request->user()->isHr(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->user()->isRecords() || $request->user()->isManager(), fn ($q) => $q->whereNotNull('user_id'))
            ->when($request->user()->isRecords() && $selectedHrUserId, fn ($q) => $q->where('user_id', $selectedHrUserId))
            ->whereIn('record_state', $visibleStates)
            ->when($request->user()->isRecords() && $selectedHrUserId, fn ($q) => $q->where('user_id', $selectedHrUserId))
            ->when($selectedStatus, function ($q, $selectedStatus) {
                match ($selectedStatus) {
                    'active'      => $q->whereIn('record_state', ['new', 'active']),
                    'in_progress' => $q->where('record_state', 'in_progress'),
                    'completed'   => $q->where('record_state', 'completed'),
                    default       => null,
                };
            })
            ->when($selectedNature, fn ($q) => $q->where('nature_of_appointment', $selectedNature))
            ->when($selectedDate,   fn ($q) => $q->whereDate('encoded_at', $selectedDate))
            ->search($request->query('q'));

        if ($request->user()->isRecords() || $request->user()->isManager()) {
            $appointmentsQuery = $appointmentsQuery
                ->whereNotNull('user_id')
                ->when(
                    $selectedTab === 'needs',
                    fn ($q) => $q->where('record_state', 'in_progress')
                                  ->where(fn ($q) => $q->whereNull('transaction_number')->orWhere('transaction_number', ''))
                )
                ->when(
                    $selectedTab === 'completed',
                    fn ($q) => $q->where('record_state', 'completed')
                                  ->whereNotNull('transaction_number')
                                  ->where('transaction_number', '<>', '')
                );
        }

        $appointments = $appointmentsQuery->orderByDesc('encoded_at')->get();

        $needsTNCount = Appointment::query()
            ->when($request->user()->isHr(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->user()->isRecords() || $request->user()->isManager(), fn ($q) => $q->whereNotNull('user_id'))
            ->when($request->user()->isRecords() && $selectedHrUserId, fn ($q) => $q->where('user_id', $selectedHrUserId))
            ->where(fn ($q) => $q->whereNull('transaction_number')->orWhere('transaction_number', ''))
            ->when($request->user()->isRecords() || $request->user()->isManager(), fn ($q) => $q->where('record_state', 'in_progress'), fn ($q) => $q->whereIn('record_state', $visibleStates))
            ->count();

        $completedCount = Appointment::query()
            ->when($request->user()->isHr(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->user()->isRecords() || $request->user()->isManager(), fn ($q) => $q->whereNotNull('user_id'))
            ->when($request->user()->isRecords() && $selectedHrUserId, fn ($q) => $q->where('user_id', $selectedHrUserId))
            ->whereIn('record_state', $visibleStates)
            ->whereNotNull('transaction_number')
            ->where('transaction_number', '<>', '')
            ->count();

        $completedTodayCount = Appointment::query()
            ->when($request->user()->isHr(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->user()->isRecords() || $request->user()->isManager(), fn ($q) => $q->whereNotNull('user_id'))
            ->when($request->user()->isRecords() && $selectedHrUserId, fn ($q) => $q->where('user_id', $selectedHrUserId))
            ->whereIn('record_state', $visibleStates)
            ->whereNotNull('transaction_number')
            ->where('transaction_number', '<>', '')
            ->whereDate('updated_at', now())
            ->count();

        $monthlyTotalCount = Appointment::query()
            ->when($request->user()->isHr(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->user()->isRecords() || $request->user()->isManager(), fn ($q) => $q->whereNotNull('user_id'))
            ->when($request->user()->isRecords() && $selectedHrUserId, fn ($q) => $q->where('user_id', $selectedHrUserId))
            ->whereIn('record_state', $visibleStates)
            ->whereYear('encoded_at', now()->year)
            ->whereMonth('encoded_at', now()->month)
            ->count();

        $selectedHrUser = $selectedHrUserId ? User::find($selectedHrUserId) : null;

        return view('appointments.index', [
            'appointments'        => $appointments,
            'availableDates'      => $availableDates,
            'selectedDate'        => $selectedDate,
            'selectedStatus'      => $selectedStatus,
            'selectedNature'      => $selectedNature,
            'selectedTab'         => $selectedTab,
            'search'              => $request->query('q'),
            'needsTNCount'        => $needsTNCount,
            'completedCount'      => $completedCount,
            'completedTodayCount' => $completedTodayCount,
            'monthlyTotalCount'   => $monthlyTotalCount,
            'hrUsers'             => $hrUsers,
            'selectedHrUser'      => $selectedHrUser,
        ]);
    }

    /**
     * Show the create appointment form.
     * HR only.
     */
    public function create(): View
    {
        $this->authorize('create', Appointment::class);

        return view('appointments.create');
    }

    /**
     * Store a new appointment record.
     * Policy: HR + Admin only.
     */
    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Appointment::class);

        $data                       = $request->validated();
        $data['encoding_personnel'] = $data['encoding_personnel'] ?? auth()->user()->name ?? 'HRMO Offline Admin';
        $data['user_id']            = auth()->id();
        $data['record_state']       = 'active';

        $appointment = Appointment::create($data);

        return redirect()
            ->route('appointments.index')
            ->with('success', "Appointment for {$appointment->full_name} was saved successfully.");
    }

    /**
     * Display a single appointment.
     * Returns JSON for AJAX requests, otherwise renders the show view.
     */
    public function show(Request $request, Appointment $appointment): View|\Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $appointment);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($appointment);
        }

        return view('appointments.show', compact('appointment'));
    }

    /**
     * Full-record update.
     * Policy: HR + Admin only — Records must use updateTransactionNumber() instead.
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
     * Records role: update ONLY the transaction_number field.
     * Policy: Records + Admin.
     */
    public function updateTransactionNumber(
        UpdateTransactionNumberRequest $request,
        Appointment                    $appointment
    ): RedirectResponse {
        $this->authorize('updateTransactionNumber', $appointment);

        $newTxn = $request->transaction_number;

        $existing = Appointment::query()
            ->where('transaction_number', $newTxn)
            ->where('id', '!=', $appointment->id)
            ->exists();

        if ($existing) {
            return redirect()
                ->back()
                ->withInput()
                ->with('duplicate_txn', $newTxn)
                ->with('duplicate_txn_name', $appointment->full_name);
        }

        $appointment->update([
            'transaction_number' => $newTxn,
            'record_state'       => 'completed',
        ]);

        return redirect()
            ->route('appointments.index')
            ->with('tn_saved', $newTxn)
            ->with('tn_name', $appointment->full_name);
    }

    /**
     * Soft-delete — moves the record to Trash.
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
     * Bulk soft-delete to History/Trash.
     * Policy: Admin only.
     */
    public function bulkDestroy(BulkDestroyAppointmentRequest $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403, 'Only Admin can delete appointments.');

        $ids = $request->validated()['ids'];

        Appointment::whereIn('id', $ids)->update(['record_state' => 'deleted']);
        Appointment::whereIn('id', $ids)->delete();

        return redirect()
            ->route('appointments.index')
            ->with('success', count($ids) > 1
                ? 'Selected appointments were moved to History.'
                : 'Selected appointment was moved to History.'
            );
    }

    /**
     * Mark an appointment as concluded — moves it into History.
     * Policy: HR + Admin.
     */
    public function conclude(ConcludeAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('archive', $appointment);

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
     */
    public function archive(Request $request): View
    {
        $this->authorize('viewAny', Appointment::class);

        $from = $request->query('from');
        $to   = $request->query('to');

        $appointments = Appointment::query()
            ->when($request->user()->isHr(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->user()->isRecords() || $request->user()->isManager(), fn ($q) => $q->whereNotNull('user_id'))
            ->where('record_state', 'completed')
            ->historyBetween($from, $to)
            ->search($request->query('q'))
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('appointments.archive', [
            'appointments' => $appointments,
            'from'         => $from,
            'to'           => $to,
            'search'       => $request->query('q'),
        ]);
    }

    /**
     * Trash bin — lists soft-deleted records.
     * Policy: Admin only.
     */
    public function trash(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);

        $trashed = Appointment::onlyTrashed()->orderByDesc('deleted_at')->get();

        return view('appointments.trash', compact('trashed'));
    }

    /**
     * Restore a soft-deleted appointment.
     */
    public function restore(Request $request, int $id): RedirectResponse
    {
        $appointment = Appointment::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $appointment);

        $appointment->restore();
        $appointment->update(['record_state' => 'active']);

        return redirect()->back()
            ->with('success', "Appointment for {$appointment->full_name} was restored.");
    }

    /**
     * Permanently delete a soft-deleted appointment.
     */
    public function forceDelete(Request $request, int $id): RedirectResponse
    {
        $appointment = Appointment::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $appointment);

        $appointment->forceDelete();

        return redirect()
            ->route('appointments.trash')
            ->with('success', 'Appointment was permanently deleted.');
    }

    // -------------------------------------------------------------------------
    // Document Downloads
    // -------------------------------------------------------------------------

    /**
     * Download the Appointment Form (AFA) as a Word document.
     * Policy: HR + Admin only.
     */
    public function exportAfa(Appointment $appointment, AppointmentFormService $service): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('print', $appointment);

        $this->trackDownload($appointment, 'afa');

        $filePath = $service->generate($appointment);
        $filename = $this->buildFilename('Appointment Form - ', $appointment->full_name, 'docx', $appointment->transaction_number ?? $appointment->id);

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Download the Appointment Processing Checklist as an Excel file.
     * Policy: HR + Admin only.
     */
    public function downloadChecklist(Appointment $appointment, AppointmentFormService $service): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('print', $appointment);

        $this->trackDownload($appointment, 'checklist');

        $filePath = $service->generateChecklist($appointment);
        $filename = $this->buildFilename('Checklist - ', $appointment->full_name, 'xlsx', $appointment->transaction_number ?? $appointment->id);

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Download the Report on Appointment Issued (RAI) as an Excel file.
     * Policy: HR + Admin only.
     */
    public function downloadRai(Appointment $appointment, AppointmentFormService $service): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('print', $appointment);

        $this->trackDownload($appointment, 'rai');

        $filePath = $service->generateRai($appointment);
        $filename = $this->buildFilename('Report on Appointment Issued - ', $appointment->full_name, 'xlsx', $appointment->transaction_number ?? $appointment->id);

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Download the Final Deliberation as a Word document.
     * Policy: HR + Admin only.
     */
    public function downloadFinalDeliberation(Appointment $appointment, AppointmentFormService $service): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('print', $appointment);

        $this->trackDownload($appointment, 'final');

        $filePath = $service->generateFinalDeliberation($appointment);
        $filename = $this->buildFilename('Final Deliberation - ', $appointment->full_name, 'docx', $appointment->transaction_number ?? $appointment->id);

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    // -------------------------------------------------------------------------
    // Bulk / CSV Export
    // -------------------------------------------------------------------------

    /**
     * Bulk export endpoint — two behaviours in one route (preserved for backward compatibility):
     *
     * POST with `ids[]`  → generates and downloads a ZIP bundle of all four document
     *                       types for every selected appointment.
     * GET (no ids)       → streams a plain CSV data export of all active appointments.
     *
     * Policy: HR + Admin only.
     */
    public function exportCsv(Request $request, DocumentExportService $exportService): \Symfony\Component\HttpFoundation\StreamedResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        abort_unless(
            $request->user()->isHr() || $request->user()->isAdmin(),
            403,
            'Only HR and Admin can export or generate appointment documents.'
        );

        $ids = $request->input('ids');

        if (is_array($ids) && count($ids) > 0) {
            return $this->exportBulkZip($ids, $exportService);
        }

        return $this->streamCsvExport();
    }

    /**
     * Generate and stream a ZIP bundle for the selected appointment IDs.
     */
    private function exportBulkZip(array $ids, DocumentExportService $exportService): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $query = Appointment::whereIn('id', $ids);

        if (auth()->user()?->isHr()) {
            $query->where('user_id', auth()->id());
        }

        $appointments = $query->get();

        if ($appointments->isEmpty()) {
            abort(404, 'No appointments found for selected IDs.');
        }

        $zipPath = $exportService->buildZip($appointments);
        $zipName = 'appointments_' . now()->format('Ymd_His') . '.zip';

        return response()->download($zipPath, $zipName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Stream a plain CSV export of all active appointments.
     */
    private function streamCsvExport(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $appointments = Appointment::query()
            ->when(auth()->user()?->isHr(), fn ($q) => $q->where('user_id', auth()->id()))
            ->active()
            ->orderByDesc('encoded_at')
            ->get();

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
                    $a->transaction_number,
                    $a->last_name,
                    $a->first_name,
                    $a->middle_name,
                    $a->position_title,
                    $a->school_district,
                    $a->nature_of_appointment,
                    $a->employee_status,
                    optional($a->date_original_appointment)->format('Y-m-d'),
                    $a->eligibility_type,
                    $a->monthly_salary,
                    $a->encoding_personnel,
                    optional($a->encoded_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'appointments_' . now()->format('Y-m-d_His') . '.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Export monitoring data for selected archived appointments using the
     * SAMPLE MONITORING.xlsx template, bundled into a ZIP archive.
     */
    public function exportMonitoringCsv(Request $request, AppointmentFormService $formService): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('viewAny', Appointment::class);

        $ids = $request->input('ids', []);

        if (empty($ids)) {
            abort(404, 'No appointments selected for monitoring export.');
        }

        $query = Appointment::whereIn('id', $ids)
            ->where('record_state', 'completed');

        if (auth()->user()?->isHr()) {
            $query->where('user_id', auth()->id());
        }

        $appointments = $query->get();

        if ($appointments->isEmpty()) {
            abort(404, 'No archived appointments found for selected IDs.');
        }

        $outputDirectory = storage_path('app/temp/appointment-forms');
        File::ensureDirectoryExists($outputDirectory);

        $files = [];

        foreach ($appointments as $appointment) {
            try {
                $path = $formService->generateMonitoring($appointment);
                $txn = $appointment->transaction_number ?? $appointment->id;
                $name = 'monitoring_' . $txn . '_' . $appointment->id . '.xlsx';
                $files[] = ['path' => $path, 'name' => $name];
            } catch (\Throwable $e) {
                \Log::error("Failed to generate monitoring for appointment {$appointment->id}: {$e->getMessage()}");
            }
        }

        if (empty($files)) {
            abort(500, 'Failed to generate any monitoring documents.');
        }

        $zipName = 'monitoring_export_' . now()->format('Ymd_His') . '.zip';
        $zipPath = $outputDirectory . DIRECTORY_SEPARATOR . $zipName;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create monitoring ZIP archive.');
        }

        foreach ($files as $file) {
            $zip->addFile($file['path'], $file['name']);
        }

        $zip->close();

        foreach ($files as $file) {
            try {
                @unlink($file['path']);
            } catch (\Throwable $e) {
                // Ignore cleanup failures
            }
        }

        return response()->download($zipPath, $zipName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }
}