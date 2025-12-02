<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Timetable PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }
        .wrapper {
            width: 100%;
        }
        .header {
            text-align: center;
            margin-bottom: 8px;
        }
        .title {
            font-weight: bold;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        th, td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
        }
        th {
            background: #f3f3f3;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div class="title">
            {{ optional($school)->name ?? 'School Name' }}
        </div>
        <div>
            {{ optional($school)->address ?? 'P.O. Box ____' }}
            @if(optional($school)->region)
                &bull; {{ $school->region }}
            @endif
        </div>
        <div class="title" style="margin-top: 4px;">
            SCHOOL GENERAL TIME TABLE
        </div>
        <div>
            Mwaka: <strong>{{ $timetable->academic_year ?? '____' }}</strong>
            &bull;
            Darasa: <strong>{{ $timetable->class_name ?? '____' }}</strong>
            &bull;
            Kipindi: <strong>{{ $timetable->term ?? '____' }}</strong>
        </div>
    </div>

    <table>
        <thead>
        <tr>
            <th>DAY</th>
            <th>FORM</th>
            <th>CLASS</th>
            <th>07:00 - 07:30</th>
            <th>07:30 - 07:50</th>
            <th>08:00 - 08:40</th>
            <th>08:40 - 09:20</th>
            <th>09:20 - 10:00</th>
            <th>10:00 - 10:40</th>
            <th>11:05 - 11:45</th>
            <th>11:45 - 12:25</th>
            <th>12:25 - 01:05</th>
            <th>01:05 - 01:20</th>
            <th>01:20 - 02:00</th>
            <th>02:00 - 02:40</th>
            <th>03:30 - 05:30</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td colspan="16" style="text-align: left;">
                This PDF is an archived copy of the timetable header. The detailed grid is currently only available in the web preview.
            </td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>
