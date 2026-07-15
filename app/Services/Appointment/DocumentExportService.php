<?php

namespace App\Services\Appointment;

use App\Models\Appointment;
use App\Services\AppointmentFormService;
use Illuminate\Support\Facades\File;
use ZipArchive;
use RuntimeException;

/**
 * Handles bulk document export (ZIP archive) for one or more appointments.
 * Extracted from AppointmentController::exportCsv() to separate concerns.
 *
 * Each appointment gets four documents: Appointment Form, Final Deliberation,
 * Checklist, and RAI — all bundled into a single ZIP download.
 */
class DocumentExportService
{
    public function __construct(
        private readonly AppointmentFormService $formService
    ) {}

    /**
     * Generate a ZIP archive containing all four document types for every
     * appointment in the given collection.
     *
     * @param  \Illuminate\Support\Collection<int, Appointment>  $appointments
     * @return string  Absolute path to the generated ZIP file
     *
     * @throws RuntimeException
     */
    public function buildZip(\Illuminate\Support\Collection $appointments): string
    {
        $outputDirectory = storage_path('app/temp/appointment-forms');
        File::ensureDirectoryExists($outputDirectory);

        $files = [];

        foreach ($appointments as $appointment) {
            $folderName = $this->buildFolderName($appointment);
            $personPart = $this->buildPersonPart($appointment);

            $files = array_merge($files, $this->generateDocumentsFor(
                $appointment,
                $folderName,
                $personPart
            ));

            $this->tryEvaluateWorkflow($appointment);
        }

        return $this->createZip($outputDirectory, $files);
    }

    /**
     * Generate all four document types for a single appointment.
     * Each failure is logged individually so one bad template doesn't abort
     * the whole bundle.
     *
     * @return array<int, array{path: string, name: string}>
     */
    private function generateDocumentsFor(
        Appointment $appointment,
        string      $folderName,
        string      $personPart
    ): array {
        $files = [];
        $txn   = $appointment->transaction_number ?? $appointment->id;

        // Appointment Form
        try {
            $path    = $this->formService->generateWithTemplateFile($appointment, 'Appointment Form Generated Template.docx');
            $files[] = ['path' => $path, 'name' => "{$folderName}/{$personPart}_Appointment.docx"];
            $this->tryMarkDownloaded($appointment, 'afa');
        } catch (\Throwable $e) {
            \Log::error("Failed to generate appointment form for {$txn}: {$e->getMessage()}");
        }

        // Final Deliberation
        try {
            $path    = $this->formService->generateFinalDeliberation($appointment);
            $files[] = ['path' => $path, 'name' => "{$folderName}/{$personPart}_FinalDeliberation.docx"];
            $this->tryMarkDownloaded($appointment, 'final');
        } catch (\Throwable $e) {
            \Log::error("Failed to generate final deliberation for {$txn}: {$e->getMessage()}");
        }

        // Checklist
        try {
            $path    = $this->formService->generateChecklist($appointment);
            $files[] = ['path' => $path, 'name' => "{$folderName}/{$personPart}_Checklist.xlsx"];
            $this->tryMarkDownloaded($appointment, 'checklist');
        } catch (\Throwable $e) {
            \Log::error("Failed to generate checklist for {$txn}: {$e->getMessage()}");
        }

        // RAI
        try {
            $path    = $this->formService->generateRai($appointment);
            $files[] = ['path' => $path, 'name' => "{$folderName}/{$personPart}_RAI.xlsx"];
            $this->tryMarkDownloaded($appointment, 'rai');
        } catch (\Throwable $e) {
            \Log::error("Failed to generate RAI for {$txn}: {$e->getMessage()}");
        }

        return $files;
    }

    /**
     * Assemble the generated files into a single ZIP archive and return its path.
     *
     * @param  array<int, array{path: string, name: string}>  $files
     */
    private function createZip(string $outputDirectory, array $files): string
    {
        $zipName = 'appointments_' . now()->format('Ymd_His') . '.zip';
        $zipPath = $outputDirectory . DIRECTORY_SEPARATOR . $zipName;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create ZIP archive.');
        }

        foreach ($files as $file) {
            $zip->addFile($file['path'], $file['name']);
        }

        $zip->close();

        // Clean up the individual temp files now that they're in the ZIP.
        foreach ($files as $file) {
            try {
                @unlink($file['path']);
            } catch (\Throwable) {
                // Silently ignore cleanup failures.
            }
        }

        return $zipPath;
    }

    /**
     * Build the per-appointment ZIP folder name (person name + TXN + date + token).
     */
    private function buildFolderName(Appointment $appointment): string
    {
        $personPart = $this->buildPersonPart($appointment);
        $txn        = $appointment->transaction_number ?? $appointment->id;
        $token      = substr(bin2hex(random_bytes(3)), 0, 6);

        return sprintf(
            '%s-%s-%s-%s-%s',
            $personPart,
            $txn,
            now()->format('Y'),
            strtoupper(now()->format('F')),
            $token
        );
    }

    /**
     * Build a filesystem-safe "LAST_FIRST_MIDDLE" string for use in filenames.
     */
    private function buildPersonPart(Appointment $appointment): string
    {
        $sanitize = fn (?string $v): string => $v
            ? preg_replace('/[^A-Za-z0-9_]/', '_', trim(str_replace(' ', '_', $v)))
            : '';

        $parts = array_filter([
            $sanitize($appointment->last_name),
            $sanitize($appointment->first_name),
            $sanitize($appointment->middle_name) ?: null,
        ]);

        return implode('_', $parts);
    }

    private function tryMarkDownloaded(Appointment $appointment, string $type): void
    {
        try {
            $appointment->markDownloaded($type);
        } catch (\Throwable $e) {
            \Log::warning("Unable to mark {$type} downloaded for appointment {$appointment->id}: {$e->getMessage()}");
        }
    }

    private function tryEvaluateWorkflow(Appointment $appointment): void
    {
        try {
            $appointment->evaluateWorkflowState();
        } catch (\Throwable $e) {
            \Log::warning("Unable to evaluate workflow state for appointment {$appointment->id}: {$e->getMessage()}");
        }
    }
}
