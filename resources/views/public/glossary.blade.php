@extends('layouts.guest')
@section('title', 'Glosarium & Edukasi — Valuasi Ekonomi')
@section('guest_content')

<section style="padding:60px 0 0;background:linear-gradient(135deg,#312e81,#4f46e5);position:relative;">
    <div style="max-width:1280px;margin:0 auto;padding:0 24px;" class="animate-fade-up">
        <div class="badge" style="background:rgba(255,255,255,0.1);color:#fff;margin-bottom:12px;">📚 Edukasi</div>
        <h1 style="font-size:40px;font-weight:800;color:#fff;margin-bottom:8px;">Glosarium & Edukasi</h1>
        <p style="color:rgba(255,255,255,0.7);font-size:16px;padding-bottom:40px;">Pahami metodologi dan konsep-konsep dasar valuasi ekonomi</p>
    </div>
</section>

<section style="padding:60px 0;">
    <div style="max-width:900px;margin:0 auto;padding:0 24px;">
        {{-- Methods --}}
        <div style="display:flex;flex-direction:column;gap:24px;margin-bottom:48px;" class="stagger">
            @php $methods = [
                ['icon'=>'🚗','title'=>'Travel Cost Method (TCM)','subtitle'=>'Metode Biaya Perjalanan','color'=>'#6366f1','bg'=>'#eef2ff',
                 'desc'=>'TCM menggunakan data biaya perjalanan pengunjung untuk mengestimasi nilai ekonomi dari atraksi wisata atau rekreasi. Semakin jauh seseorang bersedia pergi, semakin tinggi nilai yang dia berikan pada lokasi tersebut.',
                 'formula'=>'Surplus Konsumen = (Biaya Perjalanan + Biaya Waktu) × Frekuensi Kunjungan',
                 'input'=>'Jarak, biaya transportasi, biaya waktu, frekuensi kunjungan',
                 'output'=>'Total manfaat rekreasi (Direct Use Benefit - Wisata)'],
                ['icon'=>'📝','title'=>'Contingent Valuation Method (CVM)','subtitle'=>'Metode Valuasi Bersyarat','color'=>'#f72585','bg'=>'#fdf2f8',
                 'desc'=>'CVM menanyakan kepada responden berapa yang mereka bersedia bayar (WTP) untuk menjaga keberadaan sumber daya alam, bahkan jika mereka tidak akan pernah menggunakannya. Ini mengukur existence value.',
                 'formula'=>'Total Non-Use Value = Mean WTP × Total Populasi',
                 'input'=>'Willingness to Pay (WTP) dari setiap responden',
                 'output'=>'Total nilai keberadaan (Non-Use Value)'],
                ['icon'=>'🌾','title'=>'Effect on Production (EOP)','subtitle'=>'Metode Dampak Produksi','color'=>'#06d6a0','bg'=>'#ecfdf5',
                 'desc'=>'EOP mengukur perubahan nilai produksi komoditas (pertanian, perikanan, hasil hutan) akibat perubahan lingkungan. Misalnya: berapa banyak padi yang hilang karena lahan pertanian berkurang?',
                 'formula'=>'Nilai Perubahan Produksi = Δ Volume Produksi × Harga Pasar',
                 'input'=>'Volume produksi sebelum & sesudah, harga pasar komoditas',
                 'output'=>'Kerugian/keuntungan produksi (Direct Use Benefit)'],
            ]; @endphp

            @foreach($methods as $m)
            <div class="card" style="padding:32px;border-left:4px solid {{ $m['color'] }};">
                <div style="display:flex;align-items:start;gap:16px;">
                    <div style="width:56px;height:56px;border-radius:14px;background:{{ $m['bg'] }};display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0;">{{ $m['icon'] }}</div>
                    <div style="flex:1;">
                        <h2 style="font-size:20px;font-weight:700;margin-bottom:4px;">{{ $m['title'] }}</h2>
                        <h3 style="font-size:14px;font-weight:600;color:{{ $m['color'] }};margin-bottom:12px;">{{ $m['subtitle'] }}</h3>
                        <p style="font-size:14px;color:var(--text-secondary);line-height:1.7;margin-bottom:16px;">{{ $m['desc'] }}</p>
                        <div style="background:{{ $m['bg'] }};padding:14px 18px;border-radius:10px;margin-bottom:14px;">
                            <p style="font-size:12px;font-weight:700;color:{{ $m['color'] }};margin-bottom:4px;">RUMUS DASAR</p>
                            <p style="font-size:14px;font-weight:600;color:var(--text);">{{ $m['formula'] }}</p>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div style="font-size:13px;"><strong>Input:</strong> <span style="color:var(--text-secondary);">{{ $m['input'] }}</span></div>
                            <div style="font-size:13px;"><strong>Output:</strong> <span style="color:var(--text-secondary);">{{ $m['output'] }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- TEV Structure --}}
        <div class="card reveal" style="padding:32px;margin-bottom:32px;">
            <h2 style="font-size:22px;font-weight:800;margin-bottom:24px;">Struktur Total Economic Value (TEV)</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                @php $concepts = [
                    ['Total Economic Value (TEV)','Nilai ekonomi total dari sumber daya alam yang dihitung dari semua komponen manfaat dan biaya.','#6366f1'],
                    ['Direct Use Value','Nilai dari penggunaan langsung: pertanian, perikanan, wisata, rekreasi.','#3b82f6'],
                    ['Indirect Use Value','Manfaat tidak langsung: perlindungan banjir, regulasi iklim, penyimpanan karbon.','#06d6a0'],
                    ['Non-Use Value','Nilai keberadaan sumber daya alam meskipun tidak digunakan secara langsung.','#f72585'],
                    ['Benefit-Cost Ratio (BCR)','Perbandingan total manfaat dengan total biaya. BCR > 1 = menguntungkan.','#f59e0b'],
                    ['Willingness to Pay (WTP)','Jumlah maksimal yang bersedia dibayarkan seseorang untuk suatu manfaat lingkungan.','#8b5cf6'],
                ]; @endphp
                @foreach($concepts as [$title, $desc, $color])
                <div style="padding:16px;border-radius:10px;border:1px solid var(--border-light);transition:all .2s;" onmouseover="this.style.borderColor='{{ $color }}'" onmouseout="this.style.borderColor='var(--border-light)'">
                    <h3 style="font-size:15px;font-weight:700;color:{{ $color }};margin-bottom:6px;">{{ $title }}</h3>
                    <p style="font-size:13px;color:var(--text-secondary);line-height:1.6;">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- FAQ --}}
        <div class="card reveal" style="padding:32px;">
            <h2 style="font-size:22px;font-weight:800;margin-bottom:20px;">Pertanyaan Umum</h2>
            @php $faqs = [
                ['Bagaimana cara nilai-nilai ini digunakan dalam kebijakan?','Nilai ekonomi membantu pembuat kebijakan membuat keputusan yang lebih baik. Jika suatu proyek konservasi menghasilkan TEV positif dan BCR > 1, itu berarti investasi tersebut menguntungkan secara ekonomi.'],
                ['Mengapa ada tiga metode berbeda?','Berbagai jenis nilai memerlukan pendekatan berbeda. TCM bagus untuk rekreasi, CVM untuk nilai keberadaan, dan EOP untuk dampak produksi. Kombinasi ketiganya memberikan gambaran menyeluruh.'],
                ['Seberapa akurat valuasi ekonomi ini?','Akurasi tergantung pada kualitas data input dan asumsi yang digunakan. Kami selalu melaporkan metodologi dan asumsi sehingga pembaca dapat mengevaluasi reliabilitas hasilnya.'],
            ]; @endphp
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach($faqs as [$q, $a])
                <details style="border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;">
                    <summary style="padding:14px 18px;font-weight:600;font-size:14px;cursor:pointer;color:var(--primary);background:var(--surface);transition:background .2s;">{{ $q }}</summary>
                    <div style="padding:0 18px 14px;font-size:14px;color:var(--text-secondary);line-height:1.7;">{{ $a }}</div>
                </details>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection
