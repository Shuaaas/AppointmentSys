<?php

namespace App\Http\Controllers;

use App\Models\PlantillaItem;
use App\Traits\ConvertsNumbersToWords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlantillaItemController extends Controller
{
    use ConvertsNumbersToWords;

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:100',
            'field' => 'nullable|string|in:data,position,school_name,city_municipality',
        ]);

        $term = trim((string) $request->query('q', ''));

        if ($term === '') {
            return response()->json([]);
        }

        $field = $request->query('field', 'data');

        $allowedFields = ['data', 'position', 'school_name', 'city_municipality'];

        if (! in_array($field, $allowedFields, true)) {
            $field = 'data';
        }

        $results = PlantillaItem::query()
            ->where($field, 'like', "%{$term}%")
            ->orderByRaw("CASE WHEN {$field} LIKE ? THEN 0 ELSE 1 END", [$term . '%'])
            ->orderBy($field)
            ->limit(20)
            ->get(['id', 'data', 'position', 'school_name', 'city_municipality', 'position_level', 'eligibility']);

        if ($field === 'position') {
            $termLower = strtolower($term);
            $injected = [];

            if (str_contains($termLower, 'teacher vi')) {
                $injected[] = (object) [
                    'id' => null,
                    'data' => null,
                    'position' => 'Teacher VI',
                    'school_name' => null,
                    'city_municipality' => null,
                    'position_level' => null,
                    'eligibility' => null,
                ];
            }

            if (str_contains($termLower, 'teacher vii')) {
                $injected[] = (object) [
                    'id' => null,
                    'data' => null,
                    'position' => 'Teacher VII',
                    'school_name' => null,
                    'city_municipality' => null,
                    'position_level' => null,
                    'eligibility' => null,
                ];
            }

            if ($injected) {
                $results = $results->concat($injected)->unique('position')->values();
            }
        }

        return response()->json($results);
    }

    public function salary(Request $request)
    {
        $request->validate([
            'grade' => 'nullable|string|max:10',
            'step' => 'nullable|string|max:10',
        ]);

        $grade = trim((string) $request->query('grade', ''));
        $step = trim((string) $request->query('step', ''));

        if ($grade === '' || $step === '') {
            return response()->json(['amount' => null, 'words' => '']);
        }

        $gradeNumber = preg_replace('/[^0-9]/', '', $grade);
        $stepNumber = (int) preg_replace('/[^0-9]/', '', $step);

        if ($gradeNumber === '' || $stepNumber <= 0) {
            return response()->json(['amount' => null, 'words' => '']);
        }

        $amount = null;

        if (Schema::hasTable('salary_steps') && Schema::hasTable('salary_grades')) {
            $amount = DB::table('salary_steps')
                ->join('salary_grades', 'salary_steps.salary_grade_id', '=', 'salary_grades.id')
                ->where('salary_grades.grade', $gradeNumber)
                ->where('salary_steps.step', $stepNumber)
                ->value('salary_steps.amount');
        }

        if ($amount === null) {
            $fallbackAmount = $this->fallbackSalaryAmount($gradeNumber, $stepNumber);

            if ($fallbackAmount !== null) {
                $amount = $fallbackAmount;
            }
        }

        if ($amount === null) {
            return response()->json(['amount' => null, 'words' => '']);
        }

        $words = mb_strtoupper($this->numberToWords((int) $amount), 'UTF-8');

        return response()->json([
            'amount' => number_format((float) $amount, 2, '.', ''),
            'words' => $words,
        ]);
    }

    private function fallbackSalaryAmount(string $gradeNumber, int $stepNumber): ?float
    {
        $fallbacks = [
            '11' => [1 => 27800.00, 2 => 28700.00, 3 => 29600.00, 4 => 30500.00, 5 => 31400.00, 6 => 32300.00, 7 => 33200.00, 8 => 34100.00],
            '12' => [1 => 28900.00, 2 => 29800.00, 3 => 30700.00, 4 => 31600.00, 5 => 32500.00, 6 => 33400.00, 7 => 34300.00, 8 => 35200.00],
            '13' => [1 => 30000.00, 2 => 30900.00, 3 => 31800.00, 4 => 32700.00, 5 => 33600.00, 6 => 34500.00, 7 => 35400.00, 8 => 36300.00],
            '14' => [1 => 31100.00, 2 => 32000.00, 3 => 32900.00, 4 => 33800.00, 5 => 34700.00, 6 => 35600.00, 7 => 36500.00, 8 => 37400.00],
            '15' => [1 => 32200.00, 2 => 33100.00, 3 => 34000.00, 4 => 34900.00, 5 => 35800.00, 6 => 36700.00, 7 => 37600.00, 8 => 38500.00],
            '16' => [1 => 33300.00, 2 => 34200.00, 3 => 35100.00, 4 => 36000.00, 5 => 36900.00, 6 => 37800.00, 7 => 38700.00, 8 => 39600.00],
            '17' => [1 => 34400.00, 2 => 35300.00, 3 => 36200.00, 4 => 37100.00, 5 => 38000.00, 6 => 38900.00, 7 => 39800.00, 8 => 40700.00],
            '18' => [1 => 35500.00, 2 => 36400.00, 3 => 37300.00, 4 => 38200.00, 5 => 39100.00, 6 => 40000.00, 7 => 40900.00, 8 => 41800.00],
            '19' => [1 => 36600.00, 2 => 37500.00, 3 => 38400.00, 4 => 39300.00, 5 => 40200.00, 6 => 41100.00, 7 => 42000.00, 8 => 42900.00],
            '20' => [1 => 37700.00, 2 => 38600.00, 3 => 39500.00, 4 => 40400.00, 5 => 41300.00, 6 => 42200.00, 7 => 43100.00, 8 => 44000.00],
        ];

        return $fallbacks[$gradeNumber][$stepNumber] ?? null;
    }
}
