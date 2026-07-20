<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Appointment Records — Print</title>
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1f2933;
            margin: 0;
            padding: 32px;
            font-size: 13px;
            line-height: 1.4;
        }

        .print-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #1f2933;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .print-header h1 {
            font-size: 18px;
            margin: 0 0 4px;
        }

        .print-header .sub {
            color: #52606d;
            font-size: 12px;
        }

        .print-meta {
            text-align: right;
            font-size: 12px;
            color: #52606d;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        thead th {
            text-align: left;
            background: #e4e7eb;
            border: 1px solid #cbd2d9;
            padding: 8px 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        tbody td {
            border: 1px solid #cbd2d9;
            padding: 8px 10px;
            vertical-align: top;
        }

        tbody tr:nth-child(even) {
            background: #f5f7fa;
        }

        .print-footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #cbd2d9;
            font-size: 11px;
            color: #7b8794;
            text-align: center;
        }

        @media print {
            body { padding: 0; }
            .no-print, button { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">
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
</body>
</html>
