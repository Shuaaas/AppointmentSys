<?php

namespace App\Http\Controllers;

use App\Models\PlantillaItem;
use App\Traits\ConvertsNumbersToWords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $amount = DB::table('salary_steps')
            ->join('salary_grades', 'salary_steps.salary_grade_id', '=', 'salary_grades.id')
            ->where('salary_grades.grade', $gradeNumber)
            ->where('salary_steps.step', $stepNumber)
            ->value('salary_steps.amount');

        if ($amount === null) {
            return response()->json(['amount' => null, 'words' => '']);
        }

        $words = mb_strtoupper($this->numberToWords((int) $amount), 'UTF-8');

        return response()->json([
            'amount' => number_format((float) $amount, 2, '.', ''),
            'words' => $words,
        ]);
    }
}
