<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Report Card</title>
    <style>
        @page { size: A4 portrait; margin: 8mm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #111; }
        .center { text-align: center; }
        .muted { color: #555; }
        .box { border: 1px solid #888; padding: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #888; padding: 3px 5px; }
        th { background: #f3f4f6; text-align: left; }
        .handwriting { font-family: "DejaVu Sans", "Segoe Script", "Lucida Handwriting", "Brush Script MT", cursive; }
        .strong { font-weight: 700; }
        .grid2 { width: 100%; }
        .grid2 td { width: 50%; vertical-align: top; border: none; padding: 0; }
        .mt6 { margin-top: 6px; }
        .mt10 { margin-top: 8px; }
        .mt14 { margin-top: 10px; }

        table, tr, td, th, .box { page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="center">
        <div class="strong" style="font-size: 12px;">{{ $school->name ?? 'SHULE YA SEKONDARI' }}</div>
        <div class="muted">Simu: {{ $school->phone ?? '........' }}, Email: {{ $school->email ?? '........' }}</div>
        <div class="strong mt6">TAARIFA YA MAENDELEO YA MWANAFUNZI (MWAKA WA {{ $year }})</div>
    </div>

    <div class="mt10">
        <table>
            <tr>
                <td><span class="strong">Jina:</span> {{ $student->full_name ?? '' }}</td>
                <td><span class="strong">Kidato:</span> {{ $student->class_level ?? '' }} {{ $student->stream ?? '' }}</td>
            </tr>
            <tr>
                <td><span class="strong">Namba ya Mtihani:</span> {{ $student->exam_number ?? '' }}</td>
                <td><span class="strong">Mtihani:</span> {{ $exam->name ?? '' }}</td>
            </tr>
        </table>
    </div>

    <div class="mt10">
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">S/N</th>
                    <th style="width: 45%;">Somo</th>
                    <th style="width: 15%;">Alama</th>
                    <th style="width: 15%;">Gredi</th>
                    <th style="width: 15%;">Maoni</th>
                </tr>
            </thead>
            <tbody>
                @foreach(($subjects ?? []) as $i => $s)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $s['name'] ?? ($s['code'] ?? '') }}</td>
                        <td>{{ $s['marks'] ?? '' }}</td>
                        <td>{{ $s['grade'] ?? '' }}</td>
                        <td>{{ $s['comment'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt10 box">
        <span class="strong">Nafasi:</span> {{ $summary['position'] ?? '—' }} / {{ $summary['out_of'] ?? '—' }}
        <span style="margin-left: 14px;"><span class="strong">Pointi:</span> {{ $summary['points'] ?? '—' }}</span>
        <span style="margin-left: 14px;"><span class="strong">Division:</span> {{ $summary['division'] ?? '—' }}</span>
    </div>

    <div class="mt6 muted">
        Maelezo ya viwango vya ufaulu: A=75-100, B=65-74, C=45-64, D=30-44, F=0-29.
    </div>

    <div class="mt14">
        <table class="grid2">
            <tr>
                <td style="padding-right: 8px;">
                    <div class="strong">Maoni ya Mwalimu wa Darasa:</div>
                    <div class="box handwriting" style="min-height: 44px;">{{ $summary['teacher_comment'] ?? '' }}</div>
                    <div class="mt6">
                        <span class="strong">Jina/Sahihi:</span> {{ $class_teacher_name ?? '' }}
                        <span class="handwriting strong" style="margin-left: 10px;">{{ $class_teacher_name ?? '' }}</span>
                        <span style="margin-left: 10px;"><span class="strong">Tarehe:</span> {{ $print_date ?? '' }}</span>
                    </div>
                </td>
                <td style="padding-left: 8px;">
                    <div class="strong">Maoni ya Mkuu wa Shule:</div>
                    <div class="box handwriting" style="min-height: 44px;">{{ $summary['headteacher_comment'] ?? '' }}</div>
                    <div class="mt6">
                        <span class="strong">Jina/Sahihi/Muhuri:</span> {{ $head_teacher_name ?? '' }}
                        <span class="handwriting strong" style="margin-left: 10px;">{{ $head_teacher_name ?? '' }}</span>
                        <span style="margin-left: 10px;"><span class="strong">Tarehe:</span> {{ $print_date ?? '' }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
