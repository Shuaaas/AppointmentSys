<?php

namespace App\Services;

use App\Traits\ConvertsNumbersToWords;
use App\Models\Appointment;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpWord\TemplateProcessor;
use RuntimeException;

class AppointmentFormService
{
    use ConvertsNumbersToWords;

    public function __construct(
        private readonly ChecklistTemplateResolver $templateResolver
    ) {}
    /**
     * Generate the main Appointment Form (.docx) for the given appointment.
     */
    public function generate(Appointment $appointment): string
    {
        $templatePath = $this->resolveAppointmentFormTemplate($appointment);

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

    private function resolveAppointmentFormTemplate(Appointment $appointment): string
    {
        $isSubstitute = $appointment->employee_status === 'Substitute' || $appointment->employee_status === 'Provisional';
        $basePath = $isSubstitute
            ? resource_path('templates/Substitute Appointments')
            : resource_path('templates');

        $templateName = $isSubstitute ? 'SUBSTITUTE APPOINTMENT FORM.docx' : 'SAMPLE APPOINTMENT FORM.docx';

        if ($appointment->senior_high_school === 'Yes') {
            $academicStrands = ['ABM', 'HUMSS', 'STEM'];
            $strand = (string) ($appointment->senior_high_strand ?? '');

            if (in_array($strand, $academicStrands, true)) {
                $templateName = $isSubstitute ? 'SUBSTITUTE APPOINTMENT FORM FOR SHS.docx' : 'SAMPLE APPOINTMENT FORM FOR SHS.docx';
            } elseif (in_array($strand, ['SHS - TVL Track', 'SHS - Sports Track'], true)) {
                $templateName = $isSubstitute ? 'SUBSTITUTE APPOINTMENT FORM FOR TVL-SPORTS TRACK.docx' : 'SAMPLE APPOINTMENT FORM FOR TVL-SPORTS TRACK.docx';
            }
        } elseif ($appointment->senior_high_school === 'No' && $appointment->teaching_level === 'Secondary') {
            $templateName = $isSubstitute ? 'SUBSTITUTE APPOINTMENT FORM FOR HS.docx' : 'SAMPLE APPOINTMENT FORM FOR HS.docx';
        }

        return $basePath . '/' . $templateName;
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
        $templatePath = $this->checklistTemplatePath($appointment);

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

    private function checklistTemplatePath(Appointment $appointment): string
    {
        return $this->templateResolver->resolve($appointment);
    }

    /**
     * Generate the Report on Appointment Issued (.xlsx) for the given appointment.
     */
    public function generateRai(Appointment $appointment): string
    {
        $templatePath = resource_path('templates/Report on Appointment Issued_NEW.xlsx');

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

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($outputPath);

        if (! File::exists($outputPath)) {
            throw new RuntimeException('RAI could not be generated.');
        }

        return $outputPath;
    }

    /**
     * Generate a single consolidated RAI (.xlsx) for multiple appointments.
     * The first appointment's data fills any template placeholders outside the
     * data table, while every appointment is written into the repeating rows
     * inside the data table (rows 25–30 in the current template).
     */
    public function generateConsolidatedRai(\Illuminate\Support\Collection $appointments): string
    {
        $templatePath = resource_path('templates/Report on Appointment Issued_NEW.xlsx');

        if (! File::exists($templatePath)) {
            throw new RuntimeException('RAI template was not found.');
        }

        $outputPath = $this->ensureOutputDirectory()
            . DIRECTORY_SEPARATOR
            . sprintf('rai-consolidated-%s.xlsx', now()->format('YmdHis'));

        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = IOFactory::load($templatePath);
        $sheet       = $spreadsheet->getActiveSheet();

        if ($appointments->isNotEmpty()) {
            $this->replacePlaceholdersInSheet($sheet, $this->raiPlaceholderValues($appointments->first()));
        }

        $maxRows = 6;
        $count = 0;
        foreach ($appointments as $appointment) {
            if ($count >= $maxRows) {
                \Log::warning('Consolidated RAI truncated: template supports a maximum of 6 appointments per report.');
                break;
            }
            $this->writeRaiDataRow($sheet, 25 + $count, $appointment);
            $count++;
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($outputPath);

        if (! File::exists($outputPath)) {
            throw new RuntimeException('Consolidated RAI could not be generated.');
        }

        return $outputPath;
    }

    private function writeRaiDataRow(Worksheet $sheet, int $row, Appointment $appointment): void
    {
        $dateStr = $appointment->date_of_signing ? date('m/d/Y', strtotime((string) $appointment->date_of_signing)) : '';

        $isNonPermanent = $appointment->employee_status === 'Substitute' || $appointment->employee_status === 'Provisional';

        $pubFrom = $isNonPermanent ? null : $this->dateShort($appointment->publication_date_from);
        $pubTo = $isNonPermanent ? null : $this->dateShort($appointment->publication_date_to);
        $subFrom = $isNonPermanent ? $this->dateShort($appointment->substitute_from) : null;
        $subTo = $isNonPermanent ? $this->dateShort($appointment->substitute_to) : null;

        $pubRange = $this->buildDateRange($pubFrom, $pubTo, $isNonPermanent);
        $subRange = $this->buildDateRange($subFrom, $subTo, false);

        $sheet->setCellValue('A' . $row, $row - 24);
        $sheet->setCellValue('B' . $row, $dateStr);
        $sheet->setCellValue('C' . $row, $this->upper($appointment->last_name));
        $sheet->setCellValue('D' . $row, $this->upper($appointment->first_name));
        $sheet->setCellValue('E' . $row, $this->upper($appointment->extension_name ?: 'N/A'));
        $sheet->setCellValue('F' . $row, $this->upper($appointment->middle_name));
        $sheet->setCellValue('G' . $row, $this->upper($appointment->position_title));
        $sheet->setCellValue('H' . $row, $appointment->plantilla_item_number);
        $sheet->setCellValueExplicit('I' . $row, $this->salaryGrade($appointment), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('J' . $row, $this->pesoAmount($appointment->monthly_salary ?? $appointment->compensation_numbers));
        $sheet->setCellValue('K' . $row, $appointment->employee_status);
        $sheet->setCellValue('L' . $row, $subRange);
        $sheet->setCellValue('M' . $row, $this->upper($appointment->nature_of_appointment));
        $sheet->setCellValue('N' . $row, $pubRange);
        $sheet->setCellValue('O' . $row, 'N/A');
    }

    private function raiPlaceholderValues(Appointment $appointment): array
    {
        $isNonPermanent = $appointment->employee_status === 'Substitute' || $appointment->employee_status === 'Provisional';

        $pubFrom = $isNonPermanent ? null : $this->dateShort($appointment->publication_date_from);
        $pubTo = $isNonPermanent ? null : $this->dateShort($appointment->publication_date_to);
        $subFrom = $isNonPermanent ? $this->dateShort($appointment->substitute_from) : null;
        $subTo = $isNonPermanent ? $this->dateShort($appointment->substitute_to) : null;

        $pubRange = $this->buildDateRange($pubFrom, $pubTo, $isNonPermanent);
        $subRange = $this->buildDateRange($subFrom, $subTo, false);

        return [
            'last_name' => $this->upper($appointment->last_name),
            'first_name' => $this->upper($appointment->first_name),
            'middle_name' => $this->upper($appointment->middle_name),
            'middle name' => $this->upper($appointment->middle_name),
            'extension_name' => $appointment->extension_name ? $this->upper($appointment->extension_name) : 'N/A',
            'employee_name' => $this->upper($this->employeeName($appointment)),
            'position' => $this->upper($appointment->position_title),
            'plantilla_number' => $appointment->plantilla_item_number,
            'salary_grade' => $this->salaryGrade($appointment) ?: 'N/A',
            'salary' => $this->pesoAmount($appointment->monthly_salary ?? $appointment->compensation_numbers),
            'employment_status' => $appointment->employee_status,
            'appointment_nature' => $appointment->nature_of_appointment,
            'natural_vacancy' => $this->upper($appointment->natural_vacancy ?: 'N/A'),
            'date_of_appointment' => $this->date($appointment->date_original_appointment),
            'date_of_signing' => $appointment->date_of_signing ? date('m/d/Y', strtotime((string) $appointment->date_of_signing)) : '',
            'current_date' => $this->currentDateFromSigning($appointment->date_of_signing),
            'publication_date_from' => $pubFrom ?: 'N/A',
            'publication_date_to' => $pubTo ?: 'N/A',
            'publication_date_range' => $pubRange,
            'assessment_date' => $this->date($appointment->assessment_date),
            'deliberation_date' => $this->date($appointment->deliberation_date),
            'substitute_from' => $subFrom ?: 'N/A',
            'substitute_to' => $subTo ?: 'N/A',
            'substitute_date_range' => $subRange,
            'senior_high_strand' => $appointment->senior_high_strand ?: '',
            'non_teaching_result' => $this->nonTeachingResult($appointment),
        ];
    }

    private function buildDateRange(?string $from, ?string $to, bool $forceNa): string
    {
        if ($forceNa) {
            return 'N/A';
        }

        if (!$from && !$to) {
            return 'N/A';
        }

        $parts = array_filter([$from, $to]);

        return implode(' to ', $parts);
    }

    private function finalDeliberationValues(Appointment $appointment): array
    {
        $isNonPermanent = $appointment->employee_status === 'Substitute' || $appointment->employee_status === 'Provisional';

        return [
            'position' => $this->upper($appointment->position_title),
            'school' => $this->properCase($appointment->school ?: $appointment->agency_name ?? $appointment->school_district),
            'employee_name' => $this->upper($this->employeeName($appointment)),
            'date_signed' => $this->date($appointment->date_received_hr ?? now()),
            'date_of_signing' => $this->date($appointment->date_of_signing),
            'publication_date_from' => $isNonPermanent ? 'N/A' : $this->date($appointment->publication_date_from),
            'publication_date_to' => $isNonPermanent ? 'N/A' : $this->date($appointment->publication_date_to),
            'assessment_date' => $this->date($appointment->assessment_date),
            'deliberation_date' => $this->date($appointment->deliberation_date),
            'natural_vacancy' => $this->upper($appointment->natural_vacancy ?: 'N/A'),
            'date_of_appointment' => $this->date($appointment->date_original_appointment),
            'senior_high_strand' => $appointment->senior_high_strand ?: '',
            'non_teaching' => $appointment->non_teaching ?: '',
            'non_teaching_result' => $this->nonTeachingResult($appointment),
            'prepared_by' => $this->upper($appointment->prepared_by ?: ''),
        ];
    }

    /**
     * Values keyed by placeholder name (without the ${...} wrapper), used by
     * generateChecklist(). employee_name and position are upper-cased per spec.
     */
    private function checklistPlaceholderValues(Appointment $appointment): array
    {
        $isNonPermanent = $appointment->employee_status === 'Substitute' || $appointment->employee_status === 'Provisional';

        return [
            'employee_name' => $this->upper($this->checklistEmployeeName($appointment)),
            'position' => $this->upper($appointment->position_title),
            'salary_grade' => $this->salaryGrade($appointment),
            'salary' => $this->pesoAmount($appointment->monthly_salary ?? $appointment->compensation_numbers),
            'natural_vacancy' => $this->upper($appointment->natural_vacancy ?: 'N/A'),
            'date_of_appointment' => $this->date($appointment->date_original_appointment),
            'date_of_signing' => $this->properCase($this->date($appointment->date_of_signing)),
            'publication_date_from' => $isNonPermanent ? 'N/A' : $this->properCase($this->date($appointment->publication_date_from)),
            'publication_date_to' => $isNonPermanent ? 'N/A' : $this->date($appointment->publication_date_to),
            'assessment_date' => $this->date($appointment->assessment_date),
            'deliberation_date' => $this->date($appointment->deliberation_date),
            'education' => $appointment->education ?: '',
            'senior_high_strand' => $appointment->senior_high_strand ?: '',
            'strand' => $appointment->senior_high_school === 'No' ? 'N/A' : ($appointment->senior_high_strand ?: 'N/A'),
            'teaching_level' => $appointment->senior_high_school === 'No' ? ($appointment->teaching_level ?: '') : 'N/A',
            'employment_status' => $this->properCase($appointment->employee_status ?: ''),
            'appointment_nature' => $this->properCase($appointment->nature_of_appointment ?: ''),
            'non_teaching' => $appointment->non_teaching ?: '',
            'non_teaching_result' => $this->nonTeachingResult($appointment),
            'eligibility_type' => $appointment->eligibility_type ?: '',
            'eligibility' => $appointment->eligibility_type ?: '',
            'eligibility_validity' => $this->date($appointment->eligibility_validity),
            'eligibility_first_used' => $appointment->eligibility_first_used ?: '',
            'encoder_name'   => $this->upper($appointment->owner->name ?? '—'),
            'user_position'  => $this->properCase($appointment->owner->position_title ?: '—'),
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
                        $value = array_key_exists($key, $values) ? (string) $values[$key] : $matches[0];

                        if ($key === 'current_date') {
                            return $this->properCase($value);
                        }

                        if (in_array($key, ['encoder_name', 'user_position', 'employment_status', 'date_of_signing', 'publication_date_from', 'appointment_nature'])) {
                            return $value;
                        }

                        return mb_strtoupper($value, 'UTF-8');
                    },
                    $cellValue
                );

                if ($replaced !== $cellValue) {
                    $cell->setValueExplicit($replaced, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
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

                $cell->setValueExplicit(mb_strtoupper($cellValue, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
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

        $result = mb_convert_case(strtolower($value), MB_CASE_TITLE, 'UTF-8');

        $initialisms = ['NHS', 'IHS', 'IS', 'HS', 'MS', 'MES', 'CS', 'NIS', 'INHS', 'SHS', 'CID', 'OSDS', 'SGOD', 'SDO-Carmona', 'ES'];

        foreach ($initialisms as $initialism) {
            $lower = strtolower($initialism);
            $result = preg_replace('/\b' . preg_quote($lower, '/') . '\b/iu', $initialism, $result);
        }

        $result = preg_replace_callback(
            '/\bM{0,3}(?:CM|CD|D?C{0,3})(?:XC|XL|L?X{0,3})(?:IX|IV|V?I{0,3})\b/i',
            fn ($m) => strtoupper($m[0]),
            $result
        );

        return $result;
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
        $isNonPermanent = $appointment->employee_status === 'Substitute' || $appointment->employee_status === 'Provisional';

        $subFrom = $isNonPermanent ? $this->dateShort($appointment->substitute_from) : null;
        $subTo = $isNonPermanent ? $this->dateShort($appointment->substitute_to) : null;
        $subRange = $this->buildDateRange($subFrom, $subTo, false);

         return [
            'employee_name' => $this->upper($this->employeeNameForAppointmentForm($appointment)),
            'tn_number' => $this->upper($appointment->transaction_number ?: ''),
            'received_records' => $this->date($appointment->date_received_records),
            'received_hr' => $this->date($appointment->date_received_hr),
            'position' => $this->properCase($appointment->position_title),
            'salary_grade' => $this->salaryGrade($appointment),
            'salary' => $this->money($appointment->monthly_salary ?? $appointment->compensation_numbers),
            'salary_words' => $appointment->compensation_words ?: $this->salaryInWords($appointment),
            'employment_status' => $this->properCase($appointment->employee_status),
            'appointment_nature' => $this->properCase($appointment->nature_of_appointment),
            'office' => $this->properCase($appointment->department ?: $appointment->agency_name),
            'school' => $this->properCase($appointment->school ?: $appointment->agency_name),
            'district' => $this->properCase($appointment->school_district),
            'division' => $this->properCase($appointment->sector),
            'plantilla_number' => $appointment->plantilla_item_number,
            'plantilla_page_number' => $appointment->plantilla_page_number,
            'date_signed' => $this->date($appointment->date_received_hr ?? now()),
            'date_of_signing' => $this->date($appointment->date_of_signing),
            'signing_date' => $appointment->date_of_signing ? $this->dateShort($appointment->date_of_signing) : '',
            'publication_date_from' => $isNonPermanent ? '' : $this->date($appointment->publication_date_from),
            'publication_date_to' => $isNonPermanent ? '' : $this->date($appointment->publication_date_to),
            'assessment_date' => $this->date($appointment->assessment_date),
            'deliberation_date' => $this->date($appointment->deliberation_date),
            'effectivity_date' => $this->date($appointment->eligibility_validity),
            'natural_vacancy' => $this->properCase($appointment->natural_vacancy ?: 'N/A'),
            'date_of_appointment' => $this->date($appointment->date_original_appointment),
            'vice' => $this->properCase($appointment->previous_incumbent ?: 'Vacant'),
            'non_teaching' => $appointment->non_teaching ?: '',
            'non_teaching_result' => $this->nonTeachingResult($appointment),
            'senior_high_strand' => $appointment->senior_high_strand ?: '',
            'appointing_officer' => $this->properCase($appointment->incumbent),
            'hrmo' => $this->properCase($appointment->encoding_personnel ?: auth()->user()?->name),
            'tin' => $appointment->tin ?: '',
            'position_level' => $appointment->position_level ?: '',
            'sex' => $appointment->sex ?: '',
            'date_of_birth' => $this->date($appointment->date_of_birth),
            'pwd' => $appointment->pwd ?: '',
            'type_of_disability' => $appointment->type_of_disability ?: '',
            'ip_group_member' => $appointment->ip_group_member ?: '',
            'ethnicity' => $appointment->ethnicity ?: '',
            'date_last_promotion' => $this->date($appointment->date_last_promotion),
            'position_from' => $appointment->position_from ?: '',
            'substitute_from' => $subFrom ?: 'N/A',
            'substitute_to' => $subTo ?: 'N/A',
            'substitute_date_range' => $subRange,
        ];
    }

    /**
     * Generate the Monitoring document (.xlsx) for the given appointment.
     */
    public function generateMonitoring(Appointment $appointment): string
    {
        $templatePath = resource_path('templates/SAMPLE MONITORING.xlsx');

        if (! File::exists($templatePath)) {
            throw new RuntimeException('Monitoring template was not found.');
        }

        $outputPath = $this->ensureOutputDirectory()
            . DIRECTORY_SEPARATOR
            . sprintf('monitoring-%s-%s.xlsx', $appointment->id, now()->format('YmdHis'));

        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = IOFactory::load($templatePath);
        $sheet       = $spreadsheet->getActiveSheet();

        $this->replacePlaceholdersInSheet($sheet, $this->monitoringPlaceholderValues($appointment));

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($outputPath);

        if (! File::exists($outputPath)) {
            throw new RuntimeException('Monitoring document could not be generated.');
        }

        return $outputPath;
    }

    /**
     * Generate a single consolidated monitoring XLSX for multiple appointments.
     * The first appointment fills the template placeholders in row 4,
     * subsequent appointments are written into rows 5, 6, 7...
     */
    public function generateConsolidatedMonitoring(\Illuminate\Support\Collection $appointments): string
    {
        $templatePath = resource_path('templates/SAMPLE MONITORING.xlsx');

        if (! File::exists($templatePath)) {
            throw new RuntimeException('Monitoring template was not found.');
        }

        $outputPath = $this->ensureOutputDirectory()
            . DIRECTORY_SEPARATOR
            . sprintf('monitoring-consolidated-%s.xlsx', now()->format('YmdHis'));

        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = IOFactory::load($templatePath);
        $sheet       = $spreadsheet->getActiveSheet();

        $count = 0;
        foreach ($appointments as $appointment) {
            $this->writeMonitoringDataRow($sheet, 3 + $count, $appointment);
            $count++;
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($outputPath);

        if (! File::exists($outputPath)) {
            throw new RuntimeException('Consolidated monitoring could not be generated.');
        }

        return $outputPath;
    }

    private function writeMonitoringDataRow(Worksheet $sheet, int $row, Appointment $appointment): void
    {
        $values = $this->monitoringPlaceholderValues($appointment);

        $mapping = [
            'A'  => 'tn_number',
            'B'  => 'received_records',
            'C'  => 'received_hr',
            'D'  => 'school',
            'E'  => 'plantilla_number',
            'F'  => 'position_from',
            'G'  => 'position',
            'H'  => 'employee_name',
            'I'  => 'sex',
            'J'  => 'date_of_birth',
            'K'  => 'tin',
            'L'  => 'date_of_signing',
            'M'  => 'date_of_last_promotion',
            'N'  => 'eligibility_type',
            'O'  => 'eligibility_validity',
            'P'  => 'eligibility_first_used',
            'Q'  => 'salary_grade',
            'R'  => 'position_level',
            'S'  => 'appointment_nature',
            'T'  => 'employment_status',
            'U'  => 'pwd',
            'V'  => 'type_of_disability',
            'W'  => 'ip_group_member',
            'X'  => 'ethnicity',
            'Y'  => 'previous_incumbent',
            'Z'  => 'natural_vacancy',
            'AL' => 'SUBMITTED',
            'AM' => 'POSTED',
        ];

        foreach ($mapping as $col => $key) {
            $cell = $sheet->getCell($col . $row);
            $value = in_array($key, ['SUBMITTED', 'POSTED']) ? $key : ($values[$key] ?? '');
            $cell->setValueExplicit($value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            if ($col === 'H') {
                $cell->getStyle()->getFont()->getColor()->setRGB(Color::COLOR_BLACK);
            }
        }
    }

    private function monitoringPlaceholderValues(Appointment $appointment): array
    {
        return [
            'tn_number' => $this->upper($appointment->transaction_number ?: ''),
            'received_records' => $this->dateShort($appointment->date_received_records),
            'received_hr' => $this->dateShort($appointment->date_received_hr),
            'school' => $this->upper($appointment->school ?: ($appointment->agency_name ?? ($appointment->school_district ?: ''))),
            'plantilla_number' => $this->upper($appointment->plantilla_item_number ?: ''),
            'position_from' => $this->upper($appointment->position_from ?: ''),
            'position' => $this->upper($appointment->position_title),
            'employee_name' => $this->monitoringEmployeeName($appointment),
            'vice' => $this->monitoringViceName($appointment),
            'sex' => $this->upper($appointment->sex ?: ''),
            'date_of_birth' => $this->dateShort($appointment->date_of_birth),
            'tin' => $this->upper(str_replace('-', '', $appointment->tin ?: '')),
            'date_of_signing' => $this->dateShort($appointment->date_of_signing),
            'date_of_last_promotion' => $this->dateShort($appointment->date_last_promotion),
            'eligibility_type' => $this->upper($appointment->eligibility_type ?: ''),
            'eligibility_validity' => $this->dateShort($appointment->eligibility_validity),
            'eligibility_first_used' => $this->upper($appointment->eligibility_first_used ?: ''),
            'salary_grade' => $this->salaryGrade($appointment),
            'position_level' => $this->upper($appointment->position_level ?: ''),
            'appointment_nature' => $this->upper($appointment->nature_of_appointment ?: ''),
            'employment_status' => $this->upper($appointment->employee_status ?: ''),
            'pwd' => 'N/A',
            'type_of_disability' => 'N/A',
            'ip_group_member' => 'N/A',
            'ethnicity' => $this->upper($appointment->ethnicity ?: 'Tagalog'),
            'previous_incumbent' => $this->upper($appointment->previous_incumbent ?: 'Vacant'),
            'natural_vacancy' => $this->upper($appointment->natural_vacancy ?: 'N/A'),
        ];
    }

    private function monitoringEmployeeName(Appointment $appointment): string
    {
        $parts = array_filter([
            $this->upper($appointment->last_name),
            $this->upper($appointment->first_name),
            $this->upper($appointment->middle_name),
            $this->upper($appointment->extension_name),
        ]);

        if (empty($parts)) {
            return '';
        }

        $first = array_shift($parts);
        array_unshift($parts, $first . ',');

        return implode(' ', $parts);
    }

    private function monitoringViceName(Appointment $appointment): string
    {
        $parts = array_filter([
            $this->upper($appointment->first_name),
            $appointment->middle_name ? strtoupper(mb_substr($appointment->middle_name, 0, 1, 'UTF-8')) . '.' : null,
            $this->upper($appointment->last_name),
            $this->upper($appointment->extension_name),
        ]);

        return implode(' ', $parts);
    }

    private function nonTeachingResult(Appointment $appointment): string
    {
        return match ($appointment->non_teaching) {
            'Yes' => 'RUBEN E. FALTADO III',
            'No' => 'ANTONIO P. FAUSTINO JR.',
            default => '',
        };
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

    private function checklistEmployeeName(Appointment $appointment): string
    {
        $parts = collect([
            $appointment->first_name,
            $appointment->middle_name ? strtoupper(mb_substr($appointment->middle_name, 0, 1, 'UTF-8')) . '.' : null,
            $appointment->last_name,
            $appointment->extension_name,
        ])->filter();

        return $parts->implode(' ');
    }

    private function employeeNameForAppointmentForm(Appointment $appointment): string
    {
        $parts = collect([
            $appointment->first_name,
            $appointment->last_name,
            $appointment->extension_name,
        ])->filter();

        if ($appointment->middle_name) {
            $initial = strtoupper(mb_substr($appointment->middle_name, 0, 1, 'UTF-8'));
            $parts->splice(1, 0, $initial . '.');
        }

        return $parts->implode(' ');
    }

    private function salaryGrade(Appointment $appointment): string
    {
        $grade = preg_replace('/[^0-9]/', '', (string) ($appointment->salary_grade ?? ''));
        $step = preg_replace('/[^0-9]/', '', (string) ($appointment->salary_grade_step ?? ''));

        if ($grade === '' && $step === '') {
            return '';
        }

        return trim(($grade ?: '') . ($grade && $step ? '-' : '') . ($step ?: ''));
    }

    private function salaryInWords(Appointment $appointment): string
    {
        $salary = $appointment->monthly_salary ?? $appointment->compensation_numbers;

        if ($salary === null) {
            return '';
        }

        return mb_strtoupper($this->numberToWords((int) $salary), 'UTF-8');
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

    private function dateShort(mixed $value): string
    {
        if (! $value) {
            return '';
        }

        return $value instanceof \DateTimeInterface
            ? $value->format('m/d/Y')
            : date('m/d/Y', strtotime((string) $value));
    }

    private function currentDateFromSigning(mixed $dateOfSigning): string
    {
        if (! $dateOfSigning) {
            return '';
        }

        $date = $dateOfSigning instanceof \DateTimeInterface
            ? $dateOfSigning
            : date_create((string) $dateOfSigning);

        if (! $date) {
            return '';
        }

        return $date->format('F Y');
    }
}
