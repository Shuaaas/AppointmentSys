@extends('layout.app')

@section('title', 'History')
@section('page-title', 'History')

@section('content')
<div class="action-bar">
        <form class="search-wrap" method="GET" action="{{ route('history.index') }}">
        <i class="ti ti-search" aria-hidden="true"></i>
        <input type="text" name="q" value="{{ $search }}" placeholder="Search by name, school, eligibility…" class="js-autosubmit">
        <select name="user" class="js-autosubmit" aria-label="Filter by encoded by">
            <option value="">All Users</option>
            @foreach($hrUsers as $hrUser)
                <option value="{{ $hrUser->id }}" {{ $selectedUser == $hrUser->id ? 'selected' : '' }}>
                    {{ $hrUser->name }}
                </option>
            @endforeach
        </select>
        <input type="hidden" name="from" value="{{ $from }}">
        <input type="hidden" name="to" value="{{ $to }}">
    </form>

    <form class="date-control date-control-range" method="GET" action="{{ route('history.index') }}">
        <div class="date-range">
            <i class="ti ti-calendar" aria-hidden="true"></i>
            <label for="history-from">From</label>
            <input type="date" id="history-from" name="from" value="{{ $from }}">
        </div>
        <div class="date-range">
            <i class="ti ti-calendar" aria-hidden="true"></i>
            <label for="history-to">To</label>
            <input type="date" id="history-to" name="to" value="{{ $to }}">
        </div>
        <input type="hidden" name="q" value="{{ $search }}">
        <input type="hidden" name="user" value="{{ $selectedUser ?? '' }}">
        <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
        <a href="{{ route('history.index') }}" class="btn btn-sm btn-secondary">Clear</a>
    </form>
</div>

<div class="table-card">
    <div class="tbl-wrap">
        <table>
            <colgroup>
                <col style="width:38px"><col style="width:220px">
                <col style="width:170px"><col style="width:140px">
                <col style="width:120px"><col style="width:160px">
                <col style="width:150px"><col style="width:100px">
            </colgroup>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full name</th>
                    <th>School / district</th>
                    <th>Nature of appt.</th>
                    <th>Status</th>
                    <th>Date encoded</th>
                    <th>Encoded By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($history as $i => $item)
                    <tr class="data-row">
                        <td style="color:var(--text-muted)">{{ $i + 1 }}</td>
                        <td>
                            <div class="name-text">{{ $item->full_name }}</div>
                            @if (!empty($item->position_title))
                                <div class="small-text">{{ $item->position_title }}</div>
                            @endif
                        </td>
                        <td>{{ $item->school_district ?: '—' }}</td>
                        <td><span class="badge badge-teal">{{ $item->nature_of_appointment ?: '—' }}</span></td>
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
                        <td style="font-size:12px;color:var(--text-muted);">
                            {{ $item->owner->name ?? '—' }}
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
                        <td colspan="8"><p class="empty-note">No history entries found.</p></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="footer-bar">
        {{ $history->withQueryString()->links('vendor.pagination.pams') }}
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/history.js') }}"></script>
@endpush
@endsection
