@extends('layouts.admin')
@section('page_title', 'Analisis Sensitivitas')
@section('page_subtitle', 'Simulasi perubahan variabel terhadap TEV')
@section('admin_content')

<div style="display:grid;grid-template-columns:1fr 2fr;gap:24px;" class="animate-fade-up">
    {{-- Controls --}}
    <div class="card" style="position:sticky;top:100px;align-self:start;">
        <h3 style="font-size:15px;font-weight:700;margin-bottom:20px;">Parameter Simulasi</h3>

        <div class="form-group">
            <label class="form-label">Pilih Proyek</label>
            <select id="projectSelect" class="form-input" onchange="resetChart()">
                <option value="">-- Pilih Proyek --</option>
                @foreach($projects as $p)
                <option value="{{ $p->id }}" data-tev="{{ $p->tev }}" data-benefits="{{ $p->total_benefits }}" data-costs="{{ $p->total_costs }}" data-bcr="{{ $p->bcr }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Tingkat Inflasi: <span id="inflationVal">0</span>%</label>
            <input type="range" id="inflationSlider" min="-30" max="30" value="0" style="width:100%;accent-color:var(--primary);" oninput="document.getElementById('inflationVal').textContent=this.value;runSimulation()">
            <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-muted);"><span>-30%</span><span>0%</span><span>+30%</span></div>
        </div>

        <div class="form-group">
            <label class="form-label">Penyesuaian Harga Pasar: <span id="priceVal">0</span>%</label>
            <input type="range" id="priceSlider" min="-30" max="30" value="0" style="width:100%;accent-color:var(--secondary);" oninput="document.getElementById('priceVal').textContent=this.value;runSimulation()">
            <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-muted);"><span>-30%</span><span>0%</span><span>+30%</span></div>
        </div>

        <div class="form-group">
            <label class="form-label">Discount Rate: <span id="discountVal">0</span>%</label>
            <input type="range" id="discountSlider" min="0" max="15" value="0" style="width:100%;accent-color:var(--accent);" oninput="document.getElementById('discountVal').textContent=this.value;runSimulation()">
            <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-muted);"><span>0%</span><span>15%</span></div>
        </div>

        <button onclick="resetSliders()" class="btn btn-sm btn-ghost" style="width:100%;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0115.36-6.36L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 01-15.36 6.36L3 16"/><path d="M3 21v-5h5"/></svg>
            Reset Semua
        </button>
    </div>

    {{-- Results --}}
    <div>
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:24px;" id="resultCards">
            <div class="stat-card"><div class="stat-label">TEV Original</div><div class="stat-value" id="origTev" style="font-size:20px;">-</div></div>
            <div class="stat-card"><div class="stat-label">TEV Adjusted</div><div class="stat-value" id="adjTev" style="font-size:20px;color:var(--primary);">-</div></div>
            <div class="stat-card"><div class="stat-label">BCR Original</div><div class="stat-value" id="origBcr" style="font-size:20px;">-</div></div>
            <div class="stat-card"><div class="stat-label">BCR Adjusted</div><div class="stat-value" id="adjBcr" style="font-size:20px;color:var(--primary);">-</div></div>
        </div>

        <div class="card" style="margin-bottom:24px;">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">Perbandingan Original vs Adjusted</h3>
            <div style="height:300px;"><canvas id="sensitivityChart"></canvas></div>
        </div>

        <div class="card">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">Perubahan (%)</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div style="padding:16px;border-radius:var(--radius-sm);background:var(--primary-50);text-align:center;">
                    <div style="font-size:12px;color:var(--text-muted);">Δ TEV</div>
                    <div style="font-size:24px;font-weight:800;" id="tevChange">0%</div>
                </div>
                <div style="padding:16px;border-radius:var(--radius-sm);background:#ecfdf5;text-align:center;">
                    <div style="font-size:12px;color:var(--text-muted);">Δ BCR</div>
                    <div style="font-size:24px;font-weight:800;" id="bcrChange">0%</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let chart = null;
const fmt = (n) => 'Rp ' + (n/1e9).toFixed(2) + 'T';

function resetSliders() {
    ['inflationSlider','priceSlider','discountSlider'].forEach(id => { document.getElementById(id).value = 0; });
    ['inflationVal','priceVal','discountVal'].forEach(id => { document.getElementById(id).textContent = '0'; });
    runSimulation();
}

function resetChart() { if (chart) { chart.destroy(); chart = null; } runSimulation(); }

function runSimulation() {
    const sel = document.getElementById('projectSelect');
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) return;

    fetch(`/admin/sensitivity/simulate?project_id=${opt.value}&inflation_rate=${document.getElementById('inflationSlider').value}&price_adjustment=${document.getElementById('priceSlider').value}&discount_rate=${document.getElementById('discountSlider').value}`, {
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const origTev = Number(data.original.tev) || 0;
        const adjTev = Number(data.adjusted.tev) || 0;
        const origBcr = Number(data.original.bcr) || 0;
        const adjBcr = Number(data.adjusted.bcr) || 0;
        const tevPct = Number(data.changes.tev_pct) || 0;
        const bcrPct = Number(data.changes.bcr_pct) || 0;

        document.getElementById('origTev').textContent = fmt(origTev);
        document.getElementById('adjTev').textContent = fmt(adjTev);
        document.getElementById('origBcr').textContent = origBcr.toFixed(4);
        document.getElementById('adjBcr').textContent = adjBcr.toFixed(4);
        document.getElementById('tevChange').textContent = (tevPct >= 0 ? '+' : '') + tevPct + '%';
        document.getElementById('tevChange').style.color = tevPct >= 0 ? '#10b981' : '#ef4444';
        document.getElementById('bcrChange').textContent = (bcrPct >= 0 ? '+' : '') + bcrPct + '%';
        document.getElementById('bcrChange').style.color = bcrPct >= 0 ? '#10b981' : '#ef4444';

        if (chart) chart.destroy();
        chart = new Chart(document.getElementById('sensitivityChart'), {
            type: 'bar',
            data: {
                labels: ['Benefits', 'Costs', 'TEV'],
                datasets: [
                    { label: 'Original', data: [data.original.benefits/1e9, data.original.costs/1e9, data.original.tev/1e9], backgroundColor: '#6366f1', borderRadius: 6 },
                    { label: 'Adjusted', data: [data.adjusted.benefits/1e9, data.adjusted.costs/1e9, data.adjusted.tev/1e9], backgroundColor: '#06d6a0', borderRadius: 6 },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { labels: { font: { family: 'Inter' } } } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { callback: v => 'Rp' + v + 'T', font: { family: 'Inter' } } },
                    x: { grid: { display: false }, ticks: { font: { family: 'Inter', weight: 600 } } }
                }
            }
        });
    })
    .catch(err => console.error('Gagal memuat simulasi sensitivitas:', err));
}
</script>
@endsection
