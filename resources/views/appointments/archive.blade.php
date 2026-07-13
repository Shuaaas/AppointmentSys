@extends('layout.app')

@section('title', 'Archive')

@section('content')
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
        <span>Showing {{ $appointments->count() }} of {{ $appointments->count() }} result{{ $appointments->count() !== 1 ? 's' : '' }}</span>
    </div>
</div>
@endsection
