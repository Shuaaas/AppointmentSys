<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Appointment Records — Print</title>
    <link rel="stylesheet" href="{{ asset('css/print.css') }}">
</head>
<body>
    <div class="print-header">
        <div>
            <h1>Selected Appointment Records</h1>
            <div class="sub">DepEd Cavite — Personnel Appointment Management System</div>
        </div>
        <div class="print-meta">
            <div>Generated: {{ $generatedAt->format('F j, Y g:i A') }}</div>
            <div>Total records: {{ $appointments->count() }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:32px">#</th>
                <th>Full name</th>
                <th>School / district</th>
                <th>Nature of appointment</th>
                <th>Status</th>
                <th>Date encoded</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($appointments as $i => $a)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $a->full_name }}</td>
                    <td>{{ $a->school_district ?: '—' }}</td>
                    <td>{{ $a->nature_of_appointment ?: '—' }}</td>
                    <td>{{ $a->display_record_state }}</td>
                    <td>{{ optional($a->encoded_at)->format('F j, Y g:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:18px;">No records selected.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="print-footer">
        This document was generated from PAMS. Verify the records above before signing or filing.
    </div>
    <script src="{{ asset('js/print.js') }}"></script>
</body>
</html>
