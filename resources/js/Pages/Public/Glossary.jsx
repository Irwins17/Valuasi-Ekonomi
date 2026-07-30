import { Head } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import RevealOnScroll from '../../Components/effects/RevealOnScroll';

const METHODS = [
    {
        icon: '🚗', title: 'Travel Cost Method (TCM)', subtitle: 'Metode Biaya Perjalanan', color: '#6366f1', bg: '#eef2ff',
        desc: 'TCM menggunakan data biaya perjalanan pengunjung untuk mengestimasi nilai ekonomi dari atraksi wisata atau rekreasi. Semakin jauh seseorang bersedia pergi, semakin tinggi nilai yang dia berikan pada lokasi tersebut.',
        formula: 'Surplus Konsumen = (Biaya Perjalanan + Biaya Waktu) × Frekuensi Kunjungan',
        input: 'Jarak, biaya transportasi, biaya waktu, frekuensi kunjungan',
        output: 'Total manfaat rekreasi (Direct Use Benefit - Wisata)',
    },
    {
        icon: '📝', title: 'Contingent Valuation Method (CVM)', subtitle: 'Metode Valuasi Bersyarat', color: '#f72585', bg: '#fdf2f8',
        desc: 'CVM menanyakan kepada responden berapa yang mereka bersedia bayar (WTP) untuk menjaga keberadaan sumber daya alam, bahkan jika mereka tidak akan pernah menggunakannya. Ini mengukur existence value.',
        formula: 'Total Non-Use Value = Mean WTP × Total Populasi',
        input: 'Willingness to Pay (WTP) dari setiap responden',
        output: 'Total nilai keberadaan (Non-Use Value)',
    },
    {
        icon: '🌾', title: 'Effect on Production (EOP)', subtitle: 'Metode Dampak Produksi', color: '#06d6a0', bg: '#ecfdf5',
        desc: 'EOP mengukur perubahan nilai produksi komoditas (pertanian, perikanan, hasil hutan) akibat perubahan lingkungan. Misalnya: berapa banyak padi yang hilang karena lahan pertanian berkurang?',
        formula: 'Nilai Perubahan Produksi = Δ Volume Produksi × Harga Pasar',
        input: 'Volume produksi sebelum & sesudah, harga pasar komoditas',
        output: 'Kerugian/keuntungan produksi (Direct Use Benefit)',
    },
];

const CONCEPTS = [
    ['Total Economic Value (TEV)', 'Nilai ekonomi total dari sumber daya alam yang dihitung dari semua komponen manfaat dan biaya.', '#6366f1'],
    ['Direct Use Value', 'Nilai dari penggunaan langsung: pertanian, perikanan, wisata, rekreasi.', '#3b82f6'],
    ['Indirect Use Value', 'Manfaat tidak langsung: perlindungan banjir, regulasi iklim, penyimpanan karbon.', '#06d6a0'],
    ['Non-Use Value', 'Nilai keberadaan sumber daya alam meskipun tidak digunakan secara langsung.', '#f72585'],
    ['Benefit-Cost Ratio (BCR)', 'Perbandingan total manfaat dengan total biaya. BCR > 1 = menguntungkan.', '#f59e0b'],
    ['Willingness to Pay (WTP)', 'Jumlah maksimal yang bersedia dibayarkan seseorang untuk suatu manfaat lingkungan.', '#8b5cf6'],
];

const FAQS = [
    ['Bagaimana cara nilai-nilai ini digunakan dalam kebijakan?', 'Nilai ekonomi membantu pembuat kebijakan membuat keputusan yang lebih baik. Jika suatu proyek konservasi menghasilkan TEV positif dan BCR > 1, itu berarti investasi tersebut menguntungkan secara ekonomi.'],
    ['Mengapa ada tiga metode berbeda?', 'Berbagai jenis nilai memerlukan pendekatan berbeda. TCM bagus untuk rekreasi, CVM untuk nilai keberadaan, dan EOP untuk dampak produksi. Kombinasi ketiganya memberikan gambaran menyeluruh.'],
    ['Seberapa akurat valuasi ekonomi ini?', 'Akurasi tergantung pada kualitas data input dan asumsi yang digunakan. Kami selalu melaporkan metodologi dan asumsi sehingga pembaca dapat mengevaluasi reliabilitas hasilnya.'],
];

export default function Glossary() {
    return (
        <GuestLayout>
            <Head title="Glosarium & Edukasi — Valuasi Ekonomi" />

            <section style={{ padding: '60px 0 0', background: 'linear-gradient(135deg,#312e81,#4f46e5)', position: 'relative' }}>
                <div style={{ maxWidth: 1280, margin: '0 auto', padding: '0 24px' }} className="animate-fade-up">
                    <div className="badge" style={{ background: 'rgba(255,255,255,0.1)', color: '#fff', marginBottom: 12 }}>📚 Edukasi</div>
                    <h1 style={{ fontSize: 40, fontWeight: 800, color: '#fff', marginBottom: 8 }}>Glosarium & Edukasi</h1>
                    <p style={{ color: 'rgba(255,255,255,0.7)', fontSize: 16, paddingBottom: 40 }}>Pahami metodologi dan konsep-konsep dasar valuasi ekonomi</p>
                </div>
            </section>

            <section style={{ padding: '60px 0' }}>
                <div style={{ maxWidth: 900, margin: '0 auto', padding: '0 24px' }}>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 24, marginBottom: 48 }} className="stagger">
                        {METHODS.map((m) => (
                            <div className="card" key={m.title} style={{ padding: 32, borderLeft: `4px solid ${m.color}` }}>
                                <div style={{ display: 'flex', alignItems: 'flex-start', gap: 16 }}>
                                    <div style={{ width: 56, height: 56, borderRadius: 14, background: m.bg, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 28, flexShrink: 0 }}>{m.icon}</div>
                                    <div style={{ flex: 1 }}>
                                        <h2 style={{ fontSize: 20, fontWeight: 700, marginBottom: 4 }}>{m.title}</h2>
                                        <h3 style={{ fontSize: 14, fontWeight: 600, color: m.color, marginBottom: 12 }}>{m.subtitle}</h3>
                                        <p style={{ fontSize: 14, color: 'var(--text-secondary)', lineHeight: 1.7, marginBottom: 16 }}>{m.desc}</p>
                                        <div style={{ background: m.bg, padding: '14px 18px', borderRadius: 10, marginBottom: 14 }}>
                                            <p style={{ fontSize: 12, fontWeight: 700, color: m.color, marginBottom: 4 }}>RUMUS DASAR</p>
                                            <p style={{ fontSize: 14, fontWeight: 600, color: 'var(--text)' }}>{m.formula}</p>
                                        </div>
                                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                                            <div style={{ fontSize: 13 }}><strong>Input:</strong> <span style={{ color: 'var(--text-secondary)' }}>{m.input}</span></div>
                                            <div style={{ fontSize: 13 }}><strong>Output:</strong> <span style={{ color: 'var(--text-secondary)' }}>{m.output}</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>

                    <RevealOnScroll className="card" style={{ padding: 32, marginBottom: 32 }}>
                        <h2 style={{ fontSize: 22, fontWeight: 800, marginBottom: 24 }}>Struktur Total Economic Value (TEV)</h2>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            {CONCEPTS.map(([title, desc, color]) => (
                                <div
                                    key={title}
                                    style={{ padding: 16, borderRadius: 10, border: '1px solid var(--border-light)', transition: 'all .2s' }}
                                    onMouseOver={(e) => (e.currentTarget.style.borderColor = color)}
                                    onMouseOut={(e) => (e.currentTarget.style.borderColor = 'var(--border-light)')}
                                >
                                    <h3 style={{ fontSize: 15, fontWeight: 700, color, marginBottom: 6 }}>{title}</h3>
                                    <p style={{ fontSize: 13, color: 'var(--text-secondary)', lineHeight: 1.6 }}>{desc}</p>
                                </div>
                            ))}
                        </div>
                    </RevealOnScroll>

                    <RevealOnScroll className="card" style={{ padding: 32 }}>
                        <h2 style={{ fontSize: 22, fontWeight: 800, marginBottom: 20 }}>Pertanyaan Umum</h2>
                        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                            {FAQS.map(([q, a]) => (
                                <details key={q} style={{ border: '1px solid var(--border)', borderRadius: 'var(--radius-sm)', overflow: 'hidden' }}>
                                    <summary style={{ padding: '14px 18px', fontWeight: 600, fontSize: 14, cursor: 'pointer', color: 'var(--primary)', background: 'var(--surface)', transition: 'background .2s' }}>{q}</summary>
                                    <div style={{ padding: '0 18px 14px', fontSize: 14, color: 'var(--text-secondary)', lineHeight: 1.7 }}>{a}</div>
                                </details>
                            ))}
                        </div>
                    </RevealOnScroll>
                </div>
            </section>
        </GuestLayout>
    );
}
