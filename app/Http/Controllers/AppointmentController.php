<?php

namespace App\Http\Controllers;

use App\Http\Requests\Appointment\BulkDestroyAppointmentRequest;
use App\Http\Requests\Appointment\ConcludeAppointmentRequest;
use App\Http\Requests\Appointment\MarkCompletedAppointmentRequest;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
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
     * HR and Admin see all appointments via policy.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Appointment::class);

        $selectedDate   = $request->query('date');
        $selectedStatus = $request->query('status');
        $selectedNature = $request->query('nature');
        $selectedUser   = $request->query('user');

        $visibleStates = ['new', 'active', 'in_progress'];

        $baseQuery = Appointment::query()
            ->when($selectedStatus, function ($q, $selectedStatus) {
                match ($selectedStatus) {
                    'active'      => $q->whereIn('record_state', ['new', 'active']),
                    'in_progress' => $q->where('record_state', 'in_progress'),
                    'completed'   => $q->where('record_state', 'completed'),
                    default       => null,
                };
            })
            ->when($selectedNature, fn ($q) => $q->where('nature_of_appointment', $selectedNature))
            ->when($selectedUser, fn ($q) => $q->where('user_id', $selectedUser))
            ->when(! $selectedStatus, fn ($q) => $q->whereIn('record_state', $visibleStates))
            ->search($request->query('q'));

        $availableDates = (clone $baseQuery)
            ->active()
            ->selectRaw('DATE(encoded_at) as d')
            ->distinct()
            ->orderByDesc('d')
            ->pluck('d');

        $selectedDate = $selectedDate ?: null;

        $appointmentsQuery = (clone $baseQuery)
            ->when($selectedDate, fn ($q) => $q->whereDate('encoded_at', $selectedDate))
            ->orderByDesc('encoded_at');

        $appointments = $appointmentsQuery->orderByDesc('encoded_at')->get();

        $monthlyTotalCount = Appointment::query()
            ->whereYear('encoded_at', now()->year)
            ->whereMonth('encoded_at', now()->month)
            ->count();

        $hrUsers = \App\Models\User::where('role', 'hr')->get();

        return view('appointments.index', [
            'appointments'        => $appointments,
            'availableDates'      => $availableDates,
            'selectedDate'        => $selectedDate,
            'selectedStatus'      => $selectedStatus,
            'selectedNature'      => $selectedNature,
            'selectedUser'        => $selectedUser,
            'hrUsers'             => $hrUsers,
            'search'              => $request->query('q'),
            'monthlyTotalCount'   => $monthlyTotalCount,
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
     * Policy: HR + Admin only.
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
     * Bulk mark selected appointments as completed — moves them to Archive.
     * Policy: HR + Admin only.
     */
    public function markCompleted(MarkCompletedAppointmentRequest $request): RedirectResponse
    {
        $ids = $request->validated()['ids'];

        Appointment::whereIn('id', $ids)->update(['record_state' => 'completed']);

        return redirect()
            ->route('appointments.archive')
            ->with('success', count($ids) > 1
                ? 'Selected appointments were marked as completed and moved to Archive.'
                : 'Selected appointment was marked as completed and moved to Archive.'
            );
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
        $selectedUser = $request->query('user');

        $appointments = Appointment::query()
            ->where('record_state', 'completed')
            ->historyBetween($from, $to)
            ->when($selectedUser, fn ($q) => $q->where('user_id', $selectedUser))
            ->search($request->query('q'))
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        $hrUsers = \App\Models\User::where('role', 'hr')->get();

        return view('appointments.archive', [
            'appointments' => $appointments,
            'from'         => $from,
            'to'           => $to,
            'selectedUser' => $selectedUser,
            'hrUsers'      => $hrUsers,
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
        $appointments = Appointment::whereIn('id', $ids)->get();

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
     * Export monitoring data for selected archived appointments as a single
     * consolidated XLSX using the SAMPLE MONITORING.xlsx template.
     * Filename is based on the unique months of encoded_at.
     */
    public function exportMonitoringCsv(Request $request, AppointmentFormService $formService): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('viewAny', Appointment::class);

        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $ids = $request->input('ids', []);

        if (empty($ids)) {
            abort(404, 'No appointments selected for monitoring export.');
        }

        $query = Appointment::whereIn('id', $ids)
            ->where('record_state', 'completed');

        $appointments = $query->get();

        if ($appointments->isEmpty()) {
            abort(404, 'No archived appointments found for selected IDs.');
        }

        try {
            $path = $formService->generateConsolidatedMonitoring($appointments);
        } catch (\Throwable $e) {
            \Log::error('Monitoring export failed: ' . $e->getMessage(), [
                'exception' => $e,
                'ids' => $ids,
                'user_id' => auth()->id(),
            ]);
            abort(500, 'Failed to generate monitoring document. Please try again or contact support.');
        }

        $months = $appointments
            ->pluck('encoded_at')
            ->filter()
            ->unique(function ($date) {
                if ($date instanceof \DateTimeInterface) {
                    return $date->format('Y-m');
                }

                return date('Y-m', strtotime((string) $date));
            })
            ->sort()
            ->map(function ($date) {
                if ($date instanceof \DateTimeInterface) {
                    return $date->format('F');
                }

                return date('F', strtotime((string) $date));
            })
            ->implode('-');

        $filename = 'MONITORING DATA_' . $months . '.xlsx';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
