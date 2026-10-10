<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 28px 30px; }
        body { color: #17202a; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { color: #111827; font-size: 21px; margin: 0 0 5px; }
        h2 { border-bottom: 1px solid #d1d5db; color: #1f2937; font-size: 15px; margin: 22px 0 8px; padding-bottom: 6px; }
        .meta { color: #6b7280; font-size: 10px; margin-bottom: 20px; }
        .page-break { page-break-before: always; }
        table { border-collapse: collapse; table-layout: auto; width: 100%; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th { background: #e8edf2; color: #374151; font-size: 9px; text-align: left; text-transform: uppercase; }
        th, td { border: 1px solid #d1d5db; padding: 6px 7px; vertical-align: top; }
        td { overflow-wrap: anywhere; }
        .empty { color: #6b7280; padding: 12px 0; }
        .footer { border-top: 1px solid #d1d5db; color: #6b7280; font-size: 8px; margin-top: 18px; padding-top: 6px; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="meta">Report period: {{ \Illuminate\Support\Carbon::parse($from)->format('M j, Y') }} to {{ \Illuminate\Support\Carbon::parse($to)->format('M j, Y') }} · Generated {{ now()->format('M j, Y g:i A') }}</p>

    @foreach($sections as $section)
        @if(!$loop->first)<div class="page-break"></div>@endif
        <h2>{{ $section['title'] }}</h2>
        @if(count($section['data']))
            <table>
                <thead>
                    <tr>
                        @foreach($section['headers'] as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($section['data'] as $row)
                        <tr>
                            @foreach($row as $value)
                                <td>{{ is_scalar($value) ? $value : '' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="empty">No records for this date range.</p>
        @endif
    @endforeach

    <p class="footer">Neo's Pet Grooming · {{ $title }}</p>
</body>
</html>