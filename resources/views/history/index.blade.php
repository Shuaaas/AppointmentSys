@extends('layout.app')

@section('title', 'History')
@section('page-title', 'History')

@section('content')
<div class="action-bar">
    <form class="search-wrap" method="GET" action="{{ route('history.index') }}">
        <i class="ti ti-search" aria-hidden="true"></i>
        <input type="text" name="q" value="{{ $search }}" placeholder="Search by name, school, eligibility…" onchange="this.form.submit()">
    </form>

    <form class="date-control" method="GET" action="{{ route('history.index') }}">
        <div class="date-range">
            <label for="history-from">From</label>
            <input type="date" id="history-from" name="from" value="{{ $from }}">
        </div>
        <div class="date-range">
            <label for="history-to">To</label>
            <input type="date" id="history-to" name="to" value="{{ $to }}">
        </div>
        <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
        <a href="{{ route('history.index') }}" class="btn btn-sm btn-secondary" style="margin-left:8px">Clear</a>
    </form>
</div>

<div class="table-card">
    <div class="tbl-wrap">
        <table>
            <colgroup>
                <col style="width:38px"><col style="width:175px"><col style="width:125px">
                <col style="width:105px"><col style="width:105px"><col style="width:125px"><col style="width:105px"><col style="width:105px">
            </colgroup>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full name</th>
                    <th>School / district</th>
                    <th>Nature of appt.</th>
                    <th>Status</th>
                    <th>Date encoded</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($history as $i => $item)
                    <tr>
                        <td style="color:var(--text-muted)">{{ $i + 1 }}</td>
                        <td>{{ $item->full_name }}</td>
                        <td>{{ $item->school_district }}</td>
                        <td>{{ $item->nature_of_appointment }}</td>
                        <td>
                            @php
                                $statusClass = match ($item->record_state) {
                                    'deleted' => 'badge-red',
                                    'concluded', 'completed' => 'badge-green',
                                    'in_progress' => 'badge-amber',
                                    'active', 'new' => 'badge-blue',
                                    default => 'badge-blue',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">
                                {{ $item->display_record_state }}
                            </span>
                        </td>
                        <td style="font-size:12px;color:var(--text-muted);">
                            {{ optional($item->encoded_at)->format('F j, Y g:i A') }}
                        </td>
                        <td>
                            @if ($item->record_state === 'deleted')
                                <form method="POST" action="{{ route('appointments.restore', $item->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Restore</button>
                                </form>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7"><p class="empty-note">No history entries found.</p></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="footer-bar">
        {{ $history->withQueryString()->links('vendor.pagination.pams') }}
    </div>
</div>
@endsection
