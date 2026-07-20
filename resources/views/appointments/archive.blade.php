@extends('layout.app')

@section('title', 'Archive')

@section('content')
<div class="action-bar">
    <form class="search-wrap" method="GET" action="{{ route('appointments.archive') }}">
        <i class="ti ti-search" aria-hidden="true"></i>
        <input type="text" name="q" value="{{ $search }}" placeholder="Search by name, school, eligibility…" onchange="this.form.submit()">
    </form>

    <form class="date-control" method="GET" action="{{ route('appointments.archive') }}">
        <div class="date-range">
            <label for="archive-from">From</label>
            <input type="date" id="archive-from" name="from" value="{{ $from }}">
        </div>
        <div class="date-range">
            <label for="archive-to">To</label>
            <input type="date" id="archive-to" name="to" value="{{ $to }}">
        </div>
        <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
        <a href="{{ route('appointments.archive') }}" class="btn btn-sm btn-secondary" style="margin-left:8px">Clear</a>
    </form>
</div>

<div class="table-card">
    <div class="tbl-wrap">
        <table>
            <colgroup>
                <col style="width:38px"><col style="width:240px">
                <col style="width:170px"><col style="width:150px">
                <col style="width:200px"><col style="width:140px">
            </colgroup>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full name</th>
                    <th>School / district</th>
                    <th>Nature of appt.</th>
                    <th>Transaction Numbers</th>
                    <th>Date encoded</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($appointments as $i => $a)
                    <tr class="data-row">
                        <td style="color:var(--text-muted)">{{ $i + 1 }}</td>
                        <td>
                            <div class="name-text">{{ $a->full_name }}</div>
                            <div class="small-text">{{ $a->position_title }}</div>
                        </td>
                        <td>{{ $a->school_district ?: '—' }}</td>
                        <td><span class="badge badge-teal">{{ $a->nature_of_appointment ?: '—' }}</span></td>
                        <td><span class="badge badge-green">{{ $a->transaction_number ?: '—' }}</span></td>
                        <td style="font-size:12px;color:var(--text-muted)">{{ optional($a->encoded_at)->format('F j, Y') }}</td>
                    </tr>
                @empty
                    <tr class="no-rows">
                        <td colspan="6" style="border-bottom:0;padding:18px 12px;">
                            <p class="empty-note" style="margin:0;">No archived (completed) appointments found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="footer-bar">
        <span>
            @if ($appointments->total() > 0)
                Showing {{ $appointments->firstItem() }}–{{ $appointments->lastItem() }} of {{ $appointments->total() }} result{{ $appointments->total() !== 1 ? 's' : '' }}
            @else
                No results found.
            @endif
        </span>
        <div class="pagination-links">
            {{ $appointments->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
