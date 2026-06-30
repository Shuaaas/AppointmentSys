<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    /**
     * History view — shows ALL concluded appointments by default,
     * with an optional date range filter (date_concluded between from/to).
     */
    public function index(Request $request): View
    {
        $from = $request->query('from');
        $to   = $request->query('to');

        $history = Appointment::concluded()
            ->concludedBetween($from, $to)
            ->search($request->query('q'))
            ->orderByDesc('date_concluded')
            ->get();

        return view('history.index', [
            'history' => $history,
            'from'    => $from,
            'to'      => $to,
            'search'  => $request->query('q'),
        ]);
    }
}