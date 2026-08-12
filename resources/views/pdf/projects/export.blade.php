<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $project->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; color: #0f172a; font-size: 12px; }
        .header { border-bottom: 3px solid #4338ca; padding-bottom: 16px; margin-bottom: 24px; }
        .header .code { font-family: monospace; font-size: 11px; color: #64748b; }
        .header h1 { font-size: 22px; margin-top: 4px; }
        .header .meta { font-size: 12px; color: #475569; margin-top: 6px; }
        .stats { display: table; width: 100%; margin-bottom: 24px; border-collapse: collapse; }
        .stats .row { display: table-row; }
        .stats .cell { display: table-cell; width: 25%; border: 1px solid #e2e8f0; padding: 12px; text-align: center; }
        .stats .label { font-size: 10px; color: #64748b; text-transform: uppercase; margin-bottom: 4px; }
        .stats .value { font-size: 16px; font-weight: bold; color: #4338ca; }
        h2 { font-size: 14px; margin: 20px 0 8px; border-left: 3px solid #4338ca; padding-left: 8px; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.data th, table.data td { border: 1px solid #e2e8f0; padding: 6px 8px; font-size: 11px; text-align: left; }
        table.data th { background: #f8fafc; font-weight: bold; }
        table.data td.right { text-align: right; }
        .empty { color: #94a3b8; font-style: italic; padding: 8px 0; }
        .footer { margin-top: 32px; font-size: 10px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="code">{{ $project->code }}</div>
        <h1>{{ $project->name }}</h1>
        <div class="meta">{{ $project->location }} &middot; Status: {{ ucfirst(str_replace('_', ' ', $project->status)) }}</div>
        <div class="meta">
            Present Value pada tahun dasar {{ $settings->base_year }} &middot;
            discount rate {{ number_format((float) $settings->discount_rate, 2) }}% per tahun &middot;
            mata uang {{ $settings->currency }}
        </div>
    </div>

    <div class="stats">
        <div class="row">
            <div class="cell">
                <div class="label">Total Economic Value</div>
                <div class="value">Rp{{ number_format(($project->tev ?? 0) / 1e9, 2) }}T</div>
            </div>
            <div class="cell">
                <div class="label">Total Manfaat</div>
                <div class="value">Rp{{ number_format(($project->total_benefits ?? 0) / 1e9, 2) }}T</div>
            </div>
            <div class="cell">
                <div class="label">Total Biaya</div>
                <div class="value">Rp{{ number_format(($project->total_costs ?? 0) / 1e9, 2) }}T</div>
            </div>
            <div class="cell">
                <div class="label">BCR</div>
                <div class="value">{{ number_format($project->bcr ?? 0, 4) }}</div>
            </div>
        </div>
    </div>

    @if($project->description)
        <h2>Deskripsi</h2>
        <p>{{ $project->description }}</p>
    @endif

    <h2>Komponen Manfaat (Benefits)</h2>
    @if($project->benefits->count())
        <table class="data">
            <thead>
                <tr>
                    <th>Kategori</th><th>Subkategori</th><th>Deskripsi</th><th>Metode</th>
                    <th class="right">Tahun</th><th class="right">Nominal (Rp)</th><th class="right">PV (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($project->benefits as $b)
                    <tr>
                        <td>{{ str_replace('_', ' ', ucfirst($b->category)) }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($b->subcategory)) }}</td>
                        <td>{{ $b->description }}</td>
                        <td>{{ $b->method_used }}</td>
                        <td class="right">{{ $b->period_year ?? '—' }}</td>
                        <td class="right">{{ number_format($b->value) }}</td>
                        <td class="right">{{ number_format($b->pv_value) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6"><strong>Total Manfaat (PV)</strong></td>
                    <td class="right"><strong>{{ number_format($project->benefits->sum('pv_value')) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    @else
        <p class="empty">Belum ada data manfaat.</p>
    @endif

    <h2>Komponen Biaya (Costs)</h2>
    @if($project->costs->count())
        <table class="data">
            <thead>
                <tr>
                    <th>Kategori</th><th>Subkategori</th><th>Deskripsi</th><th>Kelompok Kegiatan</th>
                    <th class="right">Tahun</th><th class="right">Nominal (Rp)</th><th class="right">PV (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($project->costs as $c)
                    <tr>
                        <td>{{ str_replace('_', ' ', ucfirst($c->category)) }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($c->subcategory)) }}</td>
                        <td>{{ $c->description }}</td>
                        <td>{{ $activityGroups[$c->activity_group] ?? '—' }}</td>
                        <td class="right">{{ $c->year_applied ?? '—' }}</td>
                        <td class="right">{{ number_format($c->value) }}</td>
                        <td class="right">{{ number_format($c->pv_value) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6"><strong>Total Biaya (PV)</strong></td>
                    <td class="right"><strong>{{ number_format($project->costs->sum('pv_value')) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    @else
        <p class="empty">Belum ada data biaya.</p>
    @endif

    <div class="footer">
        Dicetak dari Valuasi Ekonomi pada {{ now()->translatedFormat('d F Y, H:i') }}
    </div>
</body>
</html>
