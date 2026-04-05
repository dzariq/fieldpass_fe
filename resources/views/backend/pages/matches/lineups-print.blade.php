<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $printHomeClub = $match->home_club;
        $printAwayClub = $match->away_club;
        $printHomeName = $printHomeClub
            ? (trim((string) ($printHomeClub->long_name ?? '')) !== ''
                ? $printHomeClub->long_name
                : ($printHomeClub->name ?? ''))
            : '';
        $printAwayName = $printAwayClub
            ? (trim((string) ($printAwayClub->long_name ?? '')) !== ''
                ? $printAwayClub->long_name
                : ($printAwayClub->name ?? ''))
            : '';
    @endphp
    <title>{{ __('Match lineups') }} — {{ $printHomeName }} vs {{ $printAwayName }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 24px;
            color: #1a2e28;
            background: #f0f4f2;
            line-height: 1.45;
        }
        .no-print {
            margin-bottom: 20px;
        }
        .no-print button {
            background: #0f4c3a;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            margin-right: 8px;
        }
        .no-print button:hover { background: #146b54; }
        .no-print a {
            color: #0f4c3a;
            font-weight: 600;
        }
        .sheet {
            max-width: 960px;
            margin: 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(15, 76, 58, 0.12);
            overflow: hidden;
            border: 1px solid #dceee6;
        }
        .sheet-header {
            background: linear-gradient(135deg, #0f4c3a 0%, #1a7f64 50%, #2d9d7a 100%);
            color: #fff;
            padding: 22px 28px;
            text-align: center;
        }
        .sheet-header h1 {
            margin: 0 0 8px;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: 0.02em;
        }
        .sheet-meta {
            font-size: 0.9rem;
            opacity: 0.95;
        }
        .sheet-meta span { margin: 0 10px; white-space: nowrap; }
        .versus {
            display: flex;
            align-items: stretch;
            justify-content: space-between;
            gap: 0;
            min-height: 280px;
        }
        .team-block {
            flex: 1;
            padding: 20px 18px;
            position: relative;
        }
        .team-block--home {
            background: linear-gradient(180deg, #f0faf7 0%, #fff 45%);
            border-right: 3px solid #0f4c3a;
        }
        .team-block--away {
            background: linear-gradient(180deg, #f5f8ff 0%, #fff 45%);
            border-left: 3px solid #2c5282;
        }
        .team-block h2 {
            margin: 0 0 14px;
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f4c3a;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(15, 76, 58, 0.2);
        }
        .team-block--away h2 { color: #2c5282; border-bottom-color: rgba(44, 82, 130, 0.25); }
        .empty-lineup {
            text-align: center;
            color: #6b7c76;
            font-style: italic;
            padding: 32px 12px;
            font-size: 0.95rem;
        }
        .section-label {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #5c6f68;
            margin: 14px 0 8px;
        }
        .section-label:first-of-type { margin-top: 0; }
        table.roster {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
        }
        table.roster th {
            text-align: left;
            padding: 6px 8px;
            background: rgba(15, 76, 58, 0.08);
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .team-block--away table.roster th { background: rgba(44, 82, 130, 0.1); }
        table.roster td {
            padding: 8px;
            border-bottom: 1px solid #eef2f0;
        }
        table.roster td.num {
            width: 42px;
            font-weight: 800;
            color: #0f4c3a;
            text-align: center;
        }
        .team-block--away table.roster td.num { color: #2c5282; }
        table.roster td.pos {
            width: 28%;
            color: #5c6f68;
            font-size: 0.82rem;
        }
        .center-pitch {
            flex: 0 0 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: repeating-linear-gradient(
                90deg,
                #e8f5e9 0px,
                #e8f5e9 8px,
                #c8e6c9 8px,
                #c8e6c9 16px
            );
            font-weight: 900;
            font-size: 0.75rem;
            color: #2e7d32;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            letter-spacing: 0.2em;
        }
        .footer-note {
            padding: 14px 24px;
            text-align: center;
            font-size: 0.8rem;
            color: #6b7c76;
            border-top: 1px solid #eef2f0;
            background: #fafcfb;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .sheet { box-shadow: none; border-radius: 0; border: none; max-width: none; }
            .team-block { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" onclick="window.print()">{{ __('Print') }}</button>
        <a href="javascript:history.back()">{{ __('Back') }}</a>
    </div>

    <div class="sheet">
        <header class="sheet-header">
            <h1>{{ $printHomeName !== '' ? $printHomeName : '—' }} <span style="opacity:.85;font-weight:600;">vs</span> {{ $printAwayName !== '' ? $printAwayName : '—' }}</h1>
            <div class="sheet-meta">
                <span>📅 {{ $kickoff->format('l, j F Y') }}</span>
                <span>🕐 {{ $kickoff->format('H:i') }}</span>
                @if($match->competition)
                    <span>🏆 {{ $match->competition->name }}</span>
                @endif
                <span>{{ __('Matchweek') }} {{ $match->matchweek ?? '—' }}</span>
            </div>
        </header>

        <div class="versus">
            <div class="team-block team-block--home">
                <h2>{{ $printHomeName !== '' ? $printHomeName : __('Home') }}</h2>
                @if(! $homeHasLineup)
                    <p class="empty-lineup">{{ __('No lineup submitted yet.') }}</p>
                @else
                    <div class="section-label">{{ __('Starting XI') }}</div>
                    <table class="roster">
                        <thead>
                            <tr>
                                <th class="num">#</th>
                                <th>{{ __('Player') }}</th>
                                <th class="pos">{{ __('Position') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($homeRoster['starters'] as $row)
                            <tr>
                                <td class="num">{{ $row['label'] }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td class="pos">{{ $row['position'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="section-label">{{ __('Substitutes') }}</div>
                    <table class="roster">
                        <thead>
                            <tr>
                                <th class="num">#</th>
                                <th>{{ __('Player') }}</th>
                                <th class="pos">{{ __('Position') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($homeRoster['subs'] as $row)
                            <tr>
                                <td class="num">{{ $row['label'] }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td class="pos">{{ $row['position'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="center-pitch">{{ __('MATCH') }}</div>

            <div class="team-block team-block--away">
                <h2>{{ $printAwayName !== '' ? $printAwayName : __('Away') }}</h2>
                @if(! $awayHasLineup)
                    <p class="empty-lineup">{{ __('No lineup submitted yet.') }}</p>
                @else
                    <div class="section-label">{{ __('Starting XI') }}</div>
                    <table class="roster">
                        <thead>
                            <tr>
                                <th class="num">#</th>
                                <th>{{ __('Player') }}</th>
                                <th class="pos">{{ __('Position') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($awayRoster['starters'] as $row)
                            <tr>
                                <td class="num">{{ $row['label'] }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td class="pos">{{ $row['position'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="section-label">{{ __('Substitutes') }}</div>
                    <table class="roster">
                        <thead>
                            <tr>
                                <th class="num">#</th>
                                <th>{{ __('Player') }}</th>
                                <th class="pos">{{ __('Position') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($awayRoster['subs'] as $row)
                            <tr>
                                <td class="num">{{ $row['label'] }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td class="pos">{{ $row['position'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <footer class="footer-note">
            {{ __('Printed from Fieldpass') }} · {{ now()->timezone('Asia/Kuala_Lumpur')->format('d M Y H:i') }}
        </footer>
    </div>
</body>
</html>
