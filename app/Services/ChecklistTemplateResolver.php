<?php

namespace App\Services;

use App\Models\Appointment;
use RuntimeException;

class ChecklistTemplateResolver
{
    public function __construct(
        private readonly string $templatesPath = ''
    ) {}

    public function resolve(Appointment $appointment): string
    {
        $position = strtoupper(trim($appointment->position_title ?? ''));
        $teachingLevel = strtoupper(trim($appointment->teaching_level ?? ''));
        $shs = strtoupper(trim($appointment->senior_high_school ?? ''));
        $strand = strtoupper(trim($appointment->senior_high_strand ?? ''));

        $basePath = $this->templatesPath ?: resource_path('templates/Checklist_Positions');

        $directMappings = [
            'ADMINISTRATIVE AIDE III' => 'Template_ADA3.xlsx',
            'ADMINISTRATIVE ASSISTANT II' => 'Template_ADAS2.xlsx',
            'ADMINISTRATIVE ASSISTANT III' => 'Template_ADAS3.xlsx',
            'ADMINISTRATIVE OFFICER II' => 'Template_AO2.xlsx',
            'ASSISTANT SCHOOL PRINCIPAL II' => 'Template_ASP2.xlsx',
            'HEAD TEACHER I' => 'Template_HT1.xlsx',
            'HEAD TEACHER II' => 'Template_HT2.xlsx',
            'HEAD TEACHER III' => 'Template_HT3.xlsx',
            'PROJECT DEVELOPMENT OFFICER I' => 'Template_PDO1.xlsx',
            'SCHOOL PRINCIPAL I' => 'Template_SP1.xlsx',
            'SCHOOL PRINCIPAL II' => 'Template_SP2.xlsx',
            'SCHOOL PRINCIPAL III' => 'Template_SP3.xlsx',
        ];

        if (isset($directMappings[$position])) {
            $path = $basePath . '/' . $directMappings[$position];
            if (file_exists($path)) {
                return $path;
            }
        }

        if (str_starts_with($position, 'MASTER TEACHER')) {
            $level = $teachingLevel === 'ELEMENTARY' ? 'ELEM' : ($teachingLevel === 'SECONDARY' ? 'SECONDARY' : null);
            if ($level) {
                $roman = trim(substr($position, 14));
                if ($roman === 'I') {
                    $filename = "Template_MT1({$level}).xlsx";
                } elseif ($roman === 'II') {
                    $filename = "Template_MT2({$level}).xlsx";
                }
            }

            if (isset($filename)) {
                $path = $basePath . '/' . $filename;
                if (file_exists($path)) {
                    return $path;
                }
            }
        }

        if (str_starts_with($position, 'SPECIAL EDUCATION TEACHER')) {
            if ($teachingLevel === 'ELEMENTARY') {
                $roman = trim(substr($position, 25));
                if ($roman === 'I') {
                    $filename = 'Template_SPET1(ELEM).xlsx';
                } elseif ($roman === 'II') {
                    $filename = 'Template_SPET2(ELEM).xlsx';
                } elseif ($roman === 'III') {
                    $filename = 'Template_SPET3(ELEM).xlsx';
                }

                if (isset($filename)) {
                    $path = $basePath . '/' . $filename;
                    if (file_exists($path)) {
                        return $path;
                    }
                }
            }
        }

        if ($position === 'SPECIAL SCIENCE TEACHER I' && $shs === 'YES' && $strand === 'STEM') {
            $path = $basePath . '/Template_SPST1(STEM).xlsx';
            if (file_exists($path)) {
                return $path;
            }
        }

        $teacherBase = null;
        if (str_starts_with($position, 'TEACHER ')) {
            $roman = trim(substr($position, 7));
            $teacherBase = $this->romanToArabic($roman);
        }

        if ($teacherBase) {
            $suffix = null;

            if ($shs === 'YES') {
                $strandMap = [
                    'ABM' => 'ABM',
                    'HUMSS' => 'HUMSS',
                    'STEM' => 'STEM',
                    'TVL TRACK' => 'TVL Track',
                    'SPORTS TRACK' => 'Sports Track',
                ];

                if (isset($strandMap[$strand])) {
                    $suffix = $strandMap[$strand];
                }
            } elseif ($shs === 'NO') {
                $suffix = $teachingLevel === 'ELEMENTARY' ? 'ELEM' : ($teachingLevel === 'SECONDARY' ? 'SECONDARY' : null);
            }

            if ($suffix) {
                $filename = "Template_T{$teacherBase}({$suffix}).xlsx";
                $path = $basePath . '/' . $filename;
                if (file_exists($path)) {
                    return $path;
                }
            }
        }

        return resource_path('templates/Checklist.xlsx');
    }

    private function romanToArabic(string $roman): ?int
    {
        $map = ['I' => 1, 'V' => 5, 'X' => 10, 'L' => 50, 'C' => 100, 'D' => 500, 'M' => 1000];
        $result = 0;
        $prev = 0;

        for ($i = strlen($roman) - 1; $i >= 0; $i--) {
            $val = $map[$roman[$i]] ?? 0;
            if ($val < $prev) {
                $result -= $val;
            } else {
                $result += $val;
            }
            $prev = $val;
        }

        return $result > 0 ? $result : null;
    }
}
