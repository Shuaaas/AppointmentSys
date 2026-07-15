<?php

namespace App\Traits;

use App\Models\Appointment;

/**
 * Provides a reusable helper for recording document download timestamps
 * and re-evaluating an appointment's workflow state afterward.
 *
 * Extracted from the 4× duplicated try/catch pattern in AppointmentController.
 */
trait TracksDocumentDownload
{
    /**
     * Mark a document type as downloaded for the given appointment, then
     * re-evaluate its workflow state. Both steps are wrapped in try/catch
     * so a logging failure never interrupts the actual file download.
     *
     * @param  Appointment  $appointment  The appointment being downloaded
     * @param  string       $type         One of: 'afa', 'checklist', 'rai', 'final'
     */
    protected function trackDownload(Appointment $appointment, string $type): void
    {
        try {
            $appointment->markDownloaded($type);
            $appointment->evaluateWorkflowState();
        } catch (\Throwable $e) {
            \Log::warning(sprintf(
                'Unable to record %s download for appointment %d: %s',
                strtoupper($type),
                $appointment->id,
                $e->getMessage()
            ));
        }
    }
}
