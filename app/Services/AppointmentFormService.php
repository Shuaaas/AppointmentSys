<?php

namespace App\Services;

use App\Traits\ConvertsNumbersToWords;
use App\Models\Appointment;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpWord\TemplateProcessor;
use RuntimeException;

class AppointmentFormService
{
    use ConvertsNumbersToWords;
    /**
     * Generate the main Appointment Form (.docx) for the given appointment.
     */
    public function generate(Appointment $appointment): string
    {
        $templatePath = resource_path('templates/Appointment Form Generated Template.docx');

        if (! File::exists($templatePath)) {
            throw new RuntimeException('Appointment form template was not found.');
        }

        $outputPath = $this->ensureOutputDirectory()
            . DIRECTORY_SEPARATOR
            . sprintf('appointment-form-%s-%s.docx', $appointment->id, now()->format('YmdHis'));

        $templateCopy = $this->normalizeTemplatePlaceholderSyntax($templatePath);
        $processor    = new TemplateProcessor($templateCopy);

        foreach ($this->placeholderValues($appointment) as $placeholder => $value) {
            $processor->setValue($placeholder, $value);
        }

        $processor->saveAs($outputPath);

        if (! File::exists($outputPath)) {
            throw new RuntimeException('Appointment form could not be generated.');
        }

        return $outputPath;
    }

    /**
     * Generate a document from a specific template filename in resources/templates.
     * Falls back to generate() when the provided template is missing.
     */
    public function generateWithTemplateFile(Appointment $appointment, string $templateFilename): string
    {
        $templatePath = resource_path('templates/' . $templateFilename);

        if (! File::exists($templatePath)) {
            return $this->generate($appointment);
        }

        $outputPath = $this->ensureOutputDirectory()
            . DIRECTORY_SEPARATOR
            . sprintf(
                '%s-%s-%s.docx',
                pathinfo($templateFilename, PATHINFO_FILENAME),
                $appointment->id,
                now()->format('YmdHis')
            );

        $templateCopy = $this->normalizeTemplatePlaceholderSyntax($templatePath);
        $processor    = new TemplateProcessor($templateCopy);

        foreach ($this->placeholderValues($appointment) as $placeholder => $value) {
            $processor->setValue($placeholder, $value);
        }

        $processor->saveAs($outputPath);

        if (! File::exists($outputPath)) {
            throw new RuntimeException('Document could not be generated from template.');
        }

        return $outputPath;
    }

    /**
     * Generate the Final Deliberation document (.docx) for the given appointment.
     */
    public function generateFinalDeliberation(Appointment $appointment): string
    {
        $templatePath = resource_path('templates/FINAL DELIBERATION_NEW TEMPLATE.docx');

        if (! File::exists($templatePath)) {
            throw new RuntimeException('Final Deliberation template was not found.');
        }

        $outputPath = $this->ensureOutputDirectory()
            . DIRECTORY_SEPARATOR
            . sprintf('final-deliberation-%s-%s.docx', $appointment->id, now()->format('YmdHis'));

        $templateCopy = $this->normalizeTemplatePlaceholderSyntax($templatePath);
        $processor    = new TemplateProcessor($templateCopy);

        foreach ($this->finalDeliberationValues($appointment) as $placeholder => $value) {
            $processor->setValue($placeholder, $value);
        }

        $processor->saveAs($outputPath);

        if (! File::exists($outputPath)) {
            throw new RuntimeException('Final Deliberation could not be generated.');
        }

        return $outputPath;
    }

    /**
     * Generate the Appointment Processing Checklist (.xlsx) for the given appointment.
     */
    public function generateChecklist(Appointment $appointment): string
    {
        $templatePath = resource_path('templates/Checklist.xlsx');

        if (! File::exists($templatePath)) {
            throw new RuntimeException('Checklist template was not found.');
        }

        $outputPath = $this->ensureOutputDirectory()
            . DIRECTORY_SEPARATOR
            . sprintf('checklist-%s-%s.xlsx', $appointment->id, now()->format('YmdHis'));

        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = IOFactory::load($templatePath);
        $sheet       = $spreadsheet->getActiveSheet();

        $this->replacePlaceholdersInSheet($sheet, $this->checklistPlaceholderValues($appointment));

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($outputPath);

        if (! File::exists($outputPath)) {
            throw new RuntimeException('Checklist could not be generated.');
        }

        return $outputPath;
    }

    /**
     * Generate the Report on Appointment Issued (.xlsx) for the given appointment.
     */
    public function generateRai(Appointment $appointment): string
    {
        $templatePath = resource_path('templates/Report on Appointment Issued.xlsx');

        if (! File::exists($templatePath)) {
            throw new RuntimeException('RAI template was not found.');
        }

        $outputPath = $this->ensureOutputDirectory()
            . DIRECTORY_SEPARATOR
            . sprintf('rai-%s-%s.xlsx', $appointment->id, now()->format('YmdHis'));

        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = IOFactory::load($templatePath);
        $sheet       = $spreadsheet->getActiveSheet();

        $this->replacePlaceholdersInSheet($sheet, $this->raiPlaceholderValues($appointment));
        $this->uppercaseSheetText($sheet);

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($outputPath);

        if (! File::exists($outputPath)) {
            throw new RuntimeException('RAI could not be generated.');
        }

        return $outputPath;
    }

    private function raiPlaceholderValues(Appointment $appointment): array
    {
        return [
            'last_name' => $this->upper($appointment->last_name),
            'first_name' => $this->upper($appointment->first_name),
            'middle_name' => $this->upper($appointment->middle_name),
            'middle name' => $this->upper($appointment->middle_name),
            'extension_name' => $this->upper($appointment->extension_name),
            'employee_name' => $this->upper($this->employeeName($appointment)),
            'position' => $this->upper($appointment->position_title),
            'salary_grade' => $this->salaryGrade($appointment),
            'salary' => $this->pesoAmount($appointment->monthly_salary ?? $appointment->compensation_numbers),
            'employment_status' => $appointment->employee_status,
            'appointment_nature' => $appointment->nature_of_appointment,
            'natural_vacancy' => $this->upper($appointment->natural_vacancy),
            'date_of_appointment' => $this->date($appointment->date_original_appointment),

        ];
    }

    private function finalDeliberationValues(Appointment $appointment): array
    {
        return [
            'position' => $this->upper($appointment->position_title),
            'school' => $this->properCase($appointment->agency_name ?? $appointment->school_district),
            'employee_name' => $this->upper($this->employeeName($appointment)),
            'date_signed' => $this->date($appointment->date_received_hr ?? now()),
            'natural_vacancy' => $this->upper($appointment->natural_vacancy),
            'date_of_appointment' => $this->date($appointment->date_original_appointment),
        ];
    }

    /**
     * Values keyed by placeholder name (without the ${...} wrapper), used by
     * generateChecklist(). employee_name and position are upper-cased per spec.
     */
    private function checklistPlaceholderValues(Appointment $appointment): array
    {
        return [
            'employee_name' => $this->upper($this->employeeName($appointment)),
            'position' => $this->upper($appointment->position_title),
            'salary' => $this->pesoAmount($appointment->monthly_salary ?? $appointment->compensation_numbers),
            'natural_vacancy' => $this->upper($appointment->natural_vacancy),
            'date_of_appointment' => $this->date($appointment->date_original_appointment),
        ];
    }

    /**
     * Scan every cell in the sheet and replace ${key} tokens with matching values.
     * Works regardless of which cells the placeholders live in, so it keeps working
     * if the template is rearranged or new ${...} placeholders are added later.
     */
    private function replacePlaceholdersInSheet(Worksheet $sheet, array $values): void
    {
        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            foreach ($cellIterator as $cell) {
                $cellValue = $cell->getValue();

                if ($cellValue instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                    $cellValue = $cellValue->getPlainText();
                }

                if (! is_string($cellValue) || ! str_contains($cellValue, '${')) {
                    continue;
                }

                $replaced = preg_replace_callback(
                    '/\$\{\s*([a-zA-Z0-9_ ]+)\s*\}/',
                    function ($matches) use ($values) {
                        $key = trim($matches[1]);

                        return array_key_exists($key, $values) ? (string) $values[$key] : $matches[0];
                    },
                    $cellValue
                );

                if ($replaced !== $cellValue) {
                    $cell->setValue($replaced);
                }
            }
        }
    }

    private function uppercaseSheetText(Worksheet $sheet): void
    {
        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            foreach ($cellIterator as $cell) {
                $cellValue = $cell->getValue();

                if ($cellValue instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                    $cellValue = $cellValue->getPlainText();
                }

                if (! is_string($cellValue) || str_starts_with($cellValue, '=')) {
                    continue;
                }

                $cell->setValue(mb_strtoupper($cellValue, 'UTF-8'));
            }
        }
    }

    private function pesoAmount(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return 'P ' . number_format((float) $value, 2);
    }

    private function upper(?string $value): string
    {
        return $value ? mb_strtoupper($value, 'UTF-8') : '';
    }

    private function properCase(?string $value): string
    {
        if (! $value) {
            return '';
        }

        return mb_convert_case(strtolower($value), MB_CASE_TITLE, 'UTF-8');
    }

    private function normalizeTemplatePlaceholderSyntax(string $templatePath): string
    {
        $normalizedPath = tempnam(sys_get_temp_dir(), 'phpword-template-');
        if ($normalizedPath === false) {
            throw new RuntimeException('Unable to create temporary template copy.');
        }

        if (! copy($templatePath, $normalizedPath)) {
            throw new RuntimeException('Unable to copy template for placeholder normalization.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($normalizedPath) !== true) {
            throw new RuntimeException('Unable to open copied template for normalization.');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (substr($name, -4) !== '.xml') {
                continue;
            }

            $content = $zip->getFromIndex($i);
            if ($content === false) {
                continue;
            }

            $clean = $this->normalizePlaceholderStrings($content);
            if ($clean !== $content) {
                $zip->deleteName($name);
                $zip->addFromString($name, $clean);
            }
        }

        $zip->close();
        return $normalizedPath;
    }

    /**
     * Ensures the temporary output directory exists and returns its path.
     * Centralizes the repeated storage_path + ensureDirectoryExists pattern.
     */
    private function ensureOutputDirectory(): string
    {
        $path = storage_path('app/temp/appointment-forms');
        File::ensureDirectoryExists($path);

        return $path;
    }

    private function normalizePlaceholderStrings(string $content): string
    {
        return preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}|%\s*([a-zA-Z0-9_]+)\s*%/',
            fn ($matches) => '${' . trim($matches[1] ?? $matches[2]) . '}',
            $content
        );
    }

    private function placeholderValues(Appointment $appointment): array
    {
        return [
            'employee_name' => $this->employeeName($appointment),
            'position' => $appointment->position_title,
            'salary_grade' => $this->salaryGrade($appointment),
            'salary' => $this->money($appointment->monthly_salary ?? $appointment->compensation_numbers),
            'salary_words' => $appointment->compensation_words ?: $this->salaryInWords($appointment),
            'employment_status' => $appointment->employee_status,
            'appointment_nature' => $appointment->nature_of_appointment,
            'office' => $appointment->department ?: $appointment->agency_name,
            'school' => $appointment->agency_name,
            'district' => $appointment->school_district,
            'division' => $appointment->sector,
            'plantilla_number' => $appointment->plantilla_item_number,
            'date_signed' => $this->date($appointment->date_received_hr ?? now()),
            'effectivity_date' => $this->date($appointment->eligibility_validity),
            'natural_vacancy' => $this->upper($appointment->natural_vacancy),
            'date_of_appointment' => $this->date($appointment->date_original_appointment),
            'vice' => $appointment->previous_incumbent,
            'appointing_officer' => $appointment->incumbent,
            'hrmo' => $appointment->encoding_personnel ?: auth()->user()?->name,
        ];
    }

    private function employeeName(Appointment $appointment): string
    {
        return collect([
            $appointment->first_name,
            $appointment->middle_name,
            $appointment->last_name,
            $appointment->extension_name,
        ])->filter()->implode(' ');
    }

    private function salaryGrade(Appointment $appointment): string
    {
        return collect([
            $appointment->salary_grade ? 'SG ' . $appointment->salary_grade : null,
            $appointment->salary_grade_step ? 'Step ' . $appointment->salary_grade_step : null,
        ])->filter()->implode(' ');
    }

    private function salaryInWords(Appointment $appointment): string
    {
        $salary = $appointment->monthly_salary ?? $appointment->compensation_numbers;

        if ($salary === null) {
            return '';
        }

        return mb_strtoupper($this->numberToWords((int) $salary) . ' pesos', 'UTF-8');
    }

    private function money(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return number_format((float) $value, 2);
    }

    private function date(mixed $value): string
    {
        if (! $value) {
            return '';
        }

        return $value instanceof \DateTimeInterface
            ? $value->format('F j, Y')
            : date('F j, Y', strtotime((string) $value));
    }
}
