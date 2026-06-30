<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AppointmentController extends Controller
{
    /**
     * List view — defaults to the latest encoded date only.
     */
    public function index(Request $request): View
    {
        $availableDates = Appointment::active()
            ->selectRaw('DATE(encoded_at) as d')
            ->distinct()
            ->orderByDesc('d')
            ->pluck('d');

        $selectedDate = $request->query('date', $availableDates->first());

        $appointments = Appointment::active()
            ->when($selectedDate, fn ($q) => $q->encodedOn($selectedDate))
            ->search($request->query('q'))
            ->orderByDesc('encoded_at')
            ->get();

        return view('appointments.index', [
            'appointments'   => $appointments,
            'availableDates' => $availableDates,
            'selectedDate'   => $selectedDate,
            'search'         => $request->query('q'),
        ]);
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['encoding_personnel'] = $data['encoding_personnel'] ?? auth()->user()->name ?? 'HRMO Offline Admin';
        $data['record_state'] = 'active';

        $appointment = Appointment::create($data);

        return redirect()
            ->route('appointments.index')
            ->with('success', "Appointment for {$appointment->full_name} was saved successfully.");
    }

    public function show(Appointment $appointment): View
    {
        return view('appointments.show', compact('appointment'));
    }

    public function update(StoreAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $appointment->update($request->validated());

        return redirect()
            ->route('appointments.index')
            ->with('success', "Appointment for {$appointment->full_name} was updated.");
    }

    /**
     * Soft delete — moves the record to Trash.
     */
    public function destroy(Appointment $appointment): RedirectResponse
    {
        $appointment->delete();

        return redirect()
            ->route('appointments.index')
            ->with('success', "Appointment for {$appointment->full_name} was moved to Trash.");
    }

    /**
     * Mark an appointment as concluded — moves it into History.
     */
    public function conclude(Request $request, Appointment $appointment): RedirectResponse
    {
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
     * Trash bin — lists soft-deleted records.
     */
    public function trash(): View
    {
        $trashed = Appointment::onlyTrashed()->orderByDesc('deleted_at')->get();

        return view('appointments.trash', compact('trashed'));
    }

    public function restore(int $id): RedirectResponse
    {
        $appointment = Appointment::onlyTrashed()->findOrFail($id);
        $appointment->restore();

        return redirect()
            ->route('appointments.trash')
            ->with('success', "Appointment for {$appointment->full_name} was restored.");
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $appointment = Appointment::onlyTrashed()->findOrFail($id);
        $appointment->forceDelete();

        return redirect()
            ->route('appointments.trash')
            ->with('success', 'Appointment was permanently deleted.');
    }

    /**
     * CSV export of all active appointments.
     */
    public function exportCsv(): StreamedResponse
    {
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