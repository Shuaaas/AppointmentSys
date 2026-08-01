<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    /**
     * History view — shows all appointments by default with an optional
     * encoded date range filter and search support.
     */
    public function index(Request $request): View
    {
        $from = $request->query('from');
        $to   = $request->query('to');
        $selectedUser = $request->query('user');

        $history = Appointment::withTrashed()
            ->historyBetween($from, $to)
            ->when($selectedUser, fn ($q) => $q->where('user_id', $selectedUser))
            ->search($request->query('q'))
            ->orderByDesc('encoded_at')
            ->paginate(15)
            ->withQueryString();

        $hrUsers = \App\Models\User::where('role', 'hr')->get();

        return view('history.index', [
            'history' => $history,
            'from'    => $from,
            'to'      => $to,
            'selectedUser' => $selectedUser,
            'hrUsers' => $hrUsers,
            'search'  => $request->query('q'),
        ]);
    }
}