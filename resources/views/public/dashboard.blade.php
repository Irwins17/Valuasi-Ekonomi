@extends('layouts.guest')
@section('title', 'Dashboard Publik — Valuasi Ekonomi')
@section('guest_content')

{{-- Header --}}
<section style="background:linear-gradient(135deg,#312e81,#4f46e5);padding:60px 0;position:relative;overflow:hidden;">
    <div style="position:absolute;bottom:0;right:0;width:300px;height:300px;background:radial-gradient(circle,rgba(6,214,160,0.15),transparent 70%);"></div>
    <div style="max-width:1280px;margin:0 auto;padding:0 24px;position:relative;z-index:1;" class="animate-fade-up">
        <div class="badge" style="background:rgba(255,255,255,0.1);color:#fff;margin-bottom:12px;">📊 Insight Only</div>
        <h1 style="font-size:40px;font-weight:800;color:#fff;margin-bottom:8px;">Dashboard Publik</h1>
        <p style="color:rgba(255,255,255,0.7);font-size:16px;">Jelajahi semua proyek valuasi ekonomi yang sudah dipublikasikan</p>
    </div>
</section>

{{-- Charts --}}
<section style="padding:60px 0;background:var(--surface-alt);">
    <div style="max-width:1280px;margin:0 auto;padding:0 24px;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:40px;" class="stagger">
            {{-- Benefit Composition --}}
            <div class="card" style="padding:28px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:24px;">Komposisi Manfaat berdasarkan Kategori</h3>
                <div style="display:flex;align-items:center;gap:32px;">
                    <div style="width:200px;height:200px;flex-shrink:0;">
                        <canvas id="benefitChart"></canvas>
                    </div>
                    <div style="flex:1;">
                        @php
                            $colors = ['direct_use' => ['#6366f1','Direct Use'], 'indirect_use' => ['#06d6a0','Indirect Use'], 'non_use' => ['#f72585','Non-Use']];
                            $totalBen = $benefits->sum('total');
                        @endphp
                        @foreach($colors as $key => [$color, $label])
                            @php $val = $benefits->firstWhere('category', $key); $pct = $totalBen > 0 ? round(($val->total ?? 0) / $totalBen * 100) : 0; @endphp
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                                <div style="width:12px;height:12px;border-radius:3px;background:{{ $color }};flex-shrink:0;"></div>
                                <div style="flex:1;">
                                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                                        <span style="font-size:13px;font-weight:500;color:var(--text);">{{ $label }}</span>
                                        <span style="font-size:13px;font-weight:700;color:{{ $color }};">{{ $pct }}%</span>
                                    </div>
                                    <div style="height:6px;background:#f1f5f9;border-radius:4px;overflow:hidden;">
                                        <div style="height:100%;width:{  $pct }}%;background:{{ $color }};border-radius:4px;transition:width 1s ease;"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Methodology Distribution --}}
            <div class="card" style="padding:28px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:24px;">Distribusi Metodologi</h3>
                <div style="height:220px;">
                    <canvas id="methodChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Projects --}}
<section style="padding:60px 0;">
    <div style="max-width:1280px;margin:0 auto;padding:0 24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;">
            <div>
                <h2 style="font-size:28px;font-weight:800;">Proyek Valuasi</h2>
                <p style="color:var(--text-secondary);font-size:14px;margin-top:4px;">{{ $projects->total() }} proyek dipublikasikan</p>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:20px;" class="stagger">
            @foreach($projects as $project)
            <div class="card" style="padding:0;overflow:hidden;">
                <div style="padding:4px 20px 0;background:linear-gradient(90deg,var(--primary),var(--secondary));height:4px;"></div>
                <div style="padding:24px;">
                    <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:12px;">
                        <div>
                            <h3 style="font-size:16px;font-weight:700;">{{ $project->name }}</h3>
                            <span style="font-size:12px;color:var(--text-muted);font-family:monospace;">{{ $project->code }}</span>
                        </div>
                        <span class="badge badge-success">Published</span>
                    </div>
                    <p style="font-size:13px;color:var(--text-secondary);margin-bottom:16px;line-height:1.6;">{{ Str::limit($project->description, 100) }}</p>
                    <div style="font-size:13px;color:var(--text-secondary);margin-bottom:16px;display:flex;align-items:center;gap:6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $project->location }}
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;padding-top:16px;border-top:1px solid var(--border-light);">
                        <div>
                            <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;">TEV</div>
                            <div style="font-size:20px;font-weight:800;color:var(--primary);">Rp{{ number_format(($project->tev ?? 0) / 1000000000, 1) }}T</div>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;">BCR</div>
                            <div style="font-size:20px;font-weight:800;color:var(--secondary);">{{ number_format($project->bcr ?? 0, 2) }}</div>
                        </div>
                    </div>
                    <a href="{{ route('public.project', $project) }}" class="btn btn-outline" style="width:100%;margin-top:16px;">Lihat Detail</a>
                </div>
            </div>
            @endforeach
        </div>

        @if($projects->hasPages())
        <div class="pagination-wrapper" style="margin-top:32px;">
            {{ $projects->links() }}
        </div>
        @endif
    </div>
</section>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Benefit Pie Chart
    new Chart(document.getElementById('benefitChart'), {
        type: 'doughnut',
        data: {
            labels: ['Direct Use', 'Indirect Use', 'Non-Use'],
            datasets: [{
                data: [
                    {{ $benefits->firstWhere('category','direct_use')->total ?? 0 }},
                    {{ $benefits->firstWhere('category','indirect_use')->total ?? 0 }},
                    {{ $benefits->firstWhere('category','non_use')->total ?? 0 }}
                ],
                backgroundColor: ['#6366f1','#06d6a0','#f72585'],
                borderWidth: 0,
                borderRadius: 4,
                spacing: 2,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            cutout: '65%',
            plugins: { legend: { display: false } },
            animation: { animateRotate: true, duration: 1500 }
        }
    });

    // Method Bar Chart
    const methodData = @json($methodDistribution);
    const methodNames = { TCM: 'Travel Cost', CVM: 'Contingent Val.', EOP: 'Effect on Prod.' };
    new Chart(document.getElementById('methodChart'), {
        type: 'bar',
        data: {
            labels: methodData.map(m => methodNames[m.method_used] || m.method_used),
            datasets: [{
                data: methodData.map(m => m.count),
                backgroundColor: ['#6366f1','#06d6a0','#f72585'],
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { family: 'Inter' } } },
                x: { grid: { display: false }, ticks: { font: { family: 'Inter', weight: 600 } } }
            },
            animation: { duration: 1500 }
        }
    });
});
</script>
@endsection
