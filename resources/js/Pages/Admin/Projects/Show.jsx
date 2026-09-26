import { useState } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import Pagination from '../../../Components/ui/Pagination';
import ConfirmDeleteButton from '../../../Components/ui/ConfirmDeleteButton';
import FormField from '../../../Components/modules/FormField';
import { formatTriliun, formatRupiah, toNumber } from '../../../lib/format';
import { ecosystemObjectTypeLabel } from '../../../lib/ecosystemObjectTypes';

const SERVICE_CATEGORY_LABELS = {
    provisioning: 'Provisioning',
    regulating: 'Regulating',
    supporting: 'Supporting',
    cultural: 'Cultural',
};

const VALUATION_FRAMEWORK = [
    {
        key: 'provisioning',
        title: 'Provisioning',
        subtitle: 'Penyediaan',
        color: '#10b981',
        items: ['Food Production', 'Raw Material', 'Genetic Resources'],
        caption: 'Manfaat langsung dari sumber daya alam.',
        icon: <><path d="M12 22v-9" /><path d="M15.5 9A5.5 5.5 0 0012 3a5.5 5.5 0 00-3.5 6" /><path d="M9 13a5 5 0 00-6 5c1 .6 2.4 1 4 1a7 7 0 005-2" /><path d="M15 13a5 5 0 016 5c-1 .6-2.4 1-4 1a7 7 0 01-5-2" /></>,
    },
    {
        key: 'regulating',
        title: 'Regulating',
        subtitle: 'Pengaturan',
        color: '#3b82f6',
        items: ['Climate (Carbon Storage)', 'Erosion Control', 'Water Supply'],
        caption: 'Proses ekologi yang mengatur lingkungan.',
        icon: <path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z" />,
    },
    {
        key: 'supporting',
        title: 'Supporting',
        subtitle: 'Pendukung',
        color: '#8b5cf6',
        items: ['Nutrient Cycling', 'Habitat'],
        caption: 'Mendukung keberlangsungan ekosistem.',
        icon: <><path d="M23 4v6h-6" /><path d="M1 20v-6h6" /><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15" /></>,
    },
    {
        key: 'cultural',
        title: 'Cultural',
        subtitle: 'Budaya',
        color: '#f59e0b',
        items: ['Recreation', 'Local Value', 'Research Location'],
        caption: 'Nilai non-materi dan budaya masyarakat.',
        icon: <><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" /><circle cx="12" cy="7" r="4" /></>,
    },
];

const METHOD_SUMMARY = [
    {
        code: 'EOP / Direct Use',
        badge: 'badge-success',
        formula: 'Nilai = (Q sudah − Q sebelum) × Harga',
        desc: 'Net DUV dihitung menggunakan biaya produksi sebagai pendekatan biaya.',
    },
    {
        code: 'TCM',
        badge: 'badge-info',
        formula: 'TC = transport + tiket + (nilai waktu × waktu tempuh)\nCS = −1 / βTC\nNilai Rekreasi = CS × jumlah pengunjung',
        desc: 'Metode Cost Surrogate berbasis biaya perjalanan.',
    },
    {
        code: 'CVM',
        badge: 'badge-purple',
        formula: 'Mean WTP = ΣWTP / n\nTotal WTP = Mean WTP × populasi',
        desc: 'Mengukur nilai non-pasar melalui kesediaan membayar.',
    },
];

const KEY_VARIABLES = [
    { label: 'Kuantitas (Q)', desc: 'volume produksi, jumlah ikan, m³ kayu, dll.', color: '#3b82f6' },
    { label: 'Harga Pasar', desc: 'harga jual komoditas/satuan.', color: '#10b981' },
    { label: 'Biaya Produksi', desc: 'input, tenaga kerja, sarana produksi.', color: '#f59e0b' },
    { label: 'Frekuensi Kunjungan', desc: 'jumlah kunjungan per periode.', color: '#8b5cf6' },
    { label: 'Biaya Perjalanan', desc: 'transport, tiket, akomodasi.', color: '#14b8a6' },
    { label: 'Waktu Tempuh', desc: 'waktu perjalanan ke lokasi.', color: '#ec4899' },
    { label: 'WTP', desc: 'nilai kesediaan membayar responden.', color: '#6366f1' },
    { label: 'Pendapatan', desc: 'pendapatan rumah tangga/bulan.', color: '#eab308' },
    { label: 'Pendidikan', desc: 'tingkat pendidikan responden.', color: '#64748b' },
];

function pct(value, total) {
    const t = toNumber(total);
    if (!t) return 0;
    return (toNumber(value) / t) * 100;
}

const MODULE_CATEGORY_COLORS = {
    provisioning: '#10b981',
    regulating: '#3b82f6',
    supporting: '#8b5cf6',
    cultural: '#f59e0b',
};

const MODULE_ICONS = {
    DUV: <><path d="M12 1v22" /><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" /></>,
    EOP: <><path d="M12 22v-9" /><path d="M15.5 9A5.5 5.5 0 0012 3a5.5 5.5 0 00-3.5 6" /><path d="M9 13a5 5 0 00-6 5c1 .6 2.4 1 4 1a7 7 0 005-2" /><path d="M15 13a5 5 0 016 5c-1 .6-2.4 1-4 1a7 7 0 01-5-2" /></>,
    TCM: <><path d="M5 17h14M5 17a2 2 0 01-2-2v-2.5L5 8h14l2 4.5V15a2 2 0 01-2 2M5 17v2a1 1 0 001 1h1a1 1 0 001-1v-2m8 0v2a1 1 0 001 1h1a1 1 0 001-1v-2" /><circle cx="7.5" cy="14.5" r="1" /><circle cx="16.5" cy="14.5" r="1" /></>,
    CVM: <><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" /><path d="M14 2v6h6" /><path d="M16 13H8" /><path d="M16 17H8" /><path d="M10 9H8" /></>,
    HPM: <><path d="M3 3v18h18" /><rect x="7" y="12" width="3" height="6" /><rect x="12" y="8" width="3" height="10" /><rect x="17" y="5" width="3" height="13" /></>,
    ABM: <><circle cx="12" cy="5" r="2" /><circle cx="5" cy="18" r="2" /><circle cx="19" cy="18" r="2" /><path d="M12 7v4M12 11l-5.5 5M12 11l5.5 5" /></>,
    CE: <><path d="M9 2v6L4 20a2 2 0 002 2h12a2 2 0 002-2L15 8V2" /><path d="M9 2h6" /><path d="M7 16h10" /></>,
    RCM: <><path d="M3 21h18" /><path d="M5 21V7l7-4 7 4v14" /><path d="M9 21v-6h6v6" /></>,
    ADC: <><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /><path d="M9.5 12l1.8 1.8L14.5 10" /></>,
    BTM: <><path d="M17 1l4 4-4 4" /><path d="M3 11V9a4 4 0 014-4h14" /><path d="M7 23l-4-4 4-4" /><path d="M21 13v2a4 4 0 01-4 4H3" /></>,
    CLIMATE: <><path d="M17.5 19a4.5 4.5 0 00.5-8.98A6 6 0 006 9.5a4.5 4.5 0 00.5 9.5z" /><path d="M8 19v2M12 19v3M16 19v2" /></>,
    EROSION: <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />,
    WATER: <path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z" />,
};

const DEFAULT_MODULE_ICON = <><rect x="3" y="3" width="7" height="7" rx="1" /><rect x="14" y="3" width="7" height="7" rx="1" /><rect x="3" y="14" width="7" height="7" rx="1" /><rect x="14" y="14" width="7" height="7" rx="1" /></>;

function ModuleStatusCard({ mod, categoryLabels }) {
    const color = MODULE_CATEGORY_COLORS[mod.service_category] || '#64748b';
    const isActive = mod.status === 'aktif';

    return (
        // Deliberately not `.card` — this sits inside one already, and nesting the
        // class doubles the 24px padding and stacks a second hover shadow on every
        // tile. A flat bordered surface with a category-coloured top edge instead,
        // matching the framework tiles further down the page.
        <div style={{
            display: 'flex', flexDirection: 'column', height: '100%',
            background: 'var(--surface)', border: '1px solid var(--border)',
            borderTop: `3px solid ${color}`, borderRadius: 10, padding: 14,
        }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: 8 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10, minWidth: 0 }}>
                    <span style={{ width: 38, height: 38, borderRadius: 10, background: `${color}1A`, color, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">{MODULE_ICONS[mod.code] || DEFAULT_MODULE_ICON}</svg>
                    </span>
                    <div style={{ minWidth: 0 }}>
                        <div style={{ fontWeight: 700, fontSize: 14, lineHeight: 1.3, overflowWrap: 'anywhere' }}>{mod.name}</div>
                        <div style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 2 }}>{mod.record_count} records</div>
                    </div>
                </div>
                <span className={`badge ${isActive ? 'badge-success' : 'badge-warning'}`} style={{ flexShrink: 0 }}>{isActive ? 'Aktif' : 'Draft'}</span>
            </div>
            {/* flex:1 so the button below lands on the card's bottom edge — without it
                the buttons sit at different heights whenever a module name wraps. */}
            <div style={{ flex: 1, fontSize: 11, color: 'var(--text-muted)', marginTop: 12, lineHeight: 1.5 }}>
                Cakupan jasa ekosistem: <strong style={{ color }}>{categoryLabels[mod.service_category] || '-'}</strong>
            </div>
            {mod.route_url ? (
                <Link href={mod.route_url} className="btn btn-sm btn-outline" style={{ marginTop: 14, width: '100%' }}>
                    Buka Modul →
                </Link>
            ) : (
                <button type="button" className="btn btn-sm btn-outline" disabled title="Halaman data modul ini belum tersedia" style={{ marginTop: 14, width: '100%', opacity: 0.45, cursor: 'not-allowed' }}>
                    Buka Modul →
                </button>
            )}
        </div>
    );
}

function EcosystemValuationSection({ ecosystemIndices }) {
    const [activeIndexNumber, setActiveIndexNumber] = useState(ecosystemIndices[0]?.index_number);
    const [expandedLandCovers, setExpandedLandCovers] = useState({});

    if (!ecosystemIndices?.length) return null;

    const current = ecosystemIndices.find((i) => i.index_number === activeIndexNumber) || ecosystemIndices[0];

    function toggleLandCover(id) {
        setExpandedLandCovers((prev) => ({ ...prev, [id]: !prev[id] }));
    }

    return (
        <div id="jasa-ekosistem" className="card" style={{ marginBottom: 24 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8, flexWrap: 'wrap', gap: 8 }}>
                <h3 style={{ fontSize: 15, fontWeight: 700 }}>Jasa Ekosistem — Valuasi Tutupan Lahan</h3>
                <div style={{ display: 'flex', gap: 6 }}>
                    {ecosystemIndices.map((idx) => (
                        <button
                            key={idx.index_number}
                            type="button"
                            className={`btn btn-sm ${idx.index_number === current.index_number ? 'btn-primary' : 'btn-outline'}`}
                            onClick={() => setActiveIndexNumber(idx.index_number)}
                        >
                            Indeks {idx.index_number}
                        </button>
                    ))}
                </div>
            </div>
            {current.notes && <p style={{ fontSize: 12, color: 'var(--text-muted)', marginBottom: 16 }}>{current.notes}</p>}

            <div className="stat-card" style={{ textAlign: 'center', marginBottom: 20, maxWidth: 280 }}>
                <div className="stat-label">TEV — {current.name}</div>
                <div className="stat-value" style={{ fontSize: 20, color: 'var(--primary)' }}>Rp{formatTriliun(current.tev, 2)}T</div>
            </div>

            {current.land_covers.map((lc) => (
                <div key={lc.id} style={{ border: '1px solid var(--border)', borderRadius: 8, padding: 12, marginBottom: 12 }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 8, flexWrap: 'wrap' }}>
                        <div>
                            <strong>{lc.name}</strong>
                            {lc.area_ha != null
                                ? <span style={{ fontSize: 12, color: 'var(--text-muted)', marginLeft: 8 }}>{formatRupiah(lc.area_ha, 2)} ha</span>
                                : <span className="badge badge-warning" style={{ marginLeft: 8 }}>Data belum lengkap</span>}
                        </div>
                        <div style={{ fontWeight: 700 }}>Rp{formatRupiah(lc.total, 0)}</div>
                    </div>
                    {lc.notes && <p style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 6 }}>{lc.notes}</p>}
                    {lc.items.length > 0 && (
                        <>
                            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(120px, 1fr))', gap: 8, marginTop: 10, fontSize: 12 }}>
                                {Object.entries(lc.by_category).filter(([, v]) => Number(v) > 0).map(([cat, val]) => (
                                    <div key={cat}>
                                        <span style={{ color: 'var(--text-muted)' }}>{SERVICE_CATEGORY_LABELS[cat]}</span><br />
                                        <strong>Rp{formatRupiah(val, 0)}</strong>
                                    </div>
                                ))}
                            </div>
                            <button type="button" className="btn btn-sm btn-ghost" style={{ marginTop: 10, padding: '4px 0' }} onClick={() => toggleLandCover(lc.id)}>
                                {expandedLandCovers[lc.id] ? 'Sembunyikan rincian' : `Lihat rincian (${lc.items.length} item)`}
                            </button>
                            {expandedLandCovers[lc.id] && (
                                <div className="table-wrapper" style={{ marginTop: 8 }}>
                                    <table className="data-table">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Jasa</th>
                                                <th style={{ textAlign: 'right' }}>Produktivitas</th>
                                                <th style={{ textAlign: 'right' }}>Harga/unit</th>
                                                <th style={{ textAlign: 'right' }}>Total Nilai Ekonomi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {lc.items.map((item) => (
                                                <tr key={item.id}>
                                                    <td style={{ fontSize: 13 }}>{item.item_name}</td>
                                                    <td style={{ fontSize: 12 }}><span className="badge badge-info">{SERVICE_CATEGORY_LABELS[item.service_category]}</span></td>
                                                    <td style={{ textAlign: 'right', fontSize: 13 }}>{formatRupiah(item.productivity_value, 2)} {item.productivity_unit}</td>
                                                    <td style={{ textAlign: 'right', fontSize: 13 }}>Rp{formatRupiah(item.unit_price, 0)}</td>
                                                    <td style={{ textAlign: 'right', fontSize: 13, fontWeight: 600 }}>Rp{formatRupiah(item.total_value, 0)}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </>
                    )}
                </div>
            ))}

            {current.cultural_items.length > 0 && (
                <div style={{ border: '1px solid var(--border)', borderRadius: 8, padding: 12 }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                        <strong>Cultural Services (tingkat kawasan)</strong>
                        <div style={{ fontWeight: 700 }}>Rp{formatRupiah(current.cultural_total, 0)}</div>
                    </div>
                    <div className="table-wrapper" style={{ marginTop: 8 }}>
                        <table className="data-table">
                            <tbody>
                                {current.cultural_items.map((item) => (
                                    <tr key={item.id}>
                                        <td style={{ fontSize: 13 }}>{item.item_name}</td>
                                        <td style={{ textAlign: 'right', fontSize: 13, fontWeight: 600 }}>Rp{formatRupiah(item.total_value, 0)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
        </div>
    );
}

/**
 * Double-counting advisories from DoubleCountingChecker.
 *
 * Deliberately loud and deliberately non-blocking: an inflated TEV looks
 * exactly like a correct one, so the overlap has to be stated here or it will
 * not be noticed — but an analyst may well have a good reason to keep both
 * rows (different areas, different commodities, a deliberate scenario), and
 * this page is in no position to overrule that.
 */
function DoubleCountingPanel({ warnings }) {
    const [expanded, setExpanded] = useState(false);

    if (!warnings?.length) return null;

    const dangers = warnings.filter((w) => w.severity === 'danger');
    const visible = expanded ? warnings : warnings.slice(0, 3);
    const worst = dangers.length ? 'danger' : 'warning';

    return (
        <div className="card" style={{ marginBottom: 24, borderLeft: `4px solid var(--${worst})` }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12, flexWrap: 'wrap', marginBottom: 12 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 9 }}>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke={`var(--${worst})`} strokeWidth="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                        <path d="M12 9v4" /><path d="M12 17h.01" />
                    </svg>
                    <h3 style={{ fontSize: 15, fontWeight: 700 }}>Peringatan Potensi Double Counting</h3>
                </div>
                <div style={{ display: 'flex', gap: 6 }}>
                    {dangers.length > 0 && <span className="badge badge-danger">{dangers.length} berisiko tinggi</span>}
                    {warnings.length - dangers.length > 0 && <span className="badge badge-warning">{warnings.length - dangers.length} perlu diperiksa</span>}
                </div>
            </div>

            <p style={{ fontSize: 12, color: 'var(--text-muted)', marginBottom: 14, lineHeight: 1.55 }}>
                Manfaat berikut berpotensi menilai nilai ekonomi yang sama lebih dari sekali, sehingga TEV bisa
                lebih tinggi dari seharusnya. Peringatan ini tidak memblokir penyimpanan — periksa dan putuskan sendiri.
            </p>

            <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                {visible.map((w, i) => (
                    <div key={`${w.code}-${w.benefit_ids.join('-')}-${i}`} className={`alert alert-${w.severity === 'danger' ? 'danger' : 'warning'}`} style={{ alignItems: 'flex-start' }}>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" style={{ flexShrink: 0, marginTop: 2 }}>
                            <circle cx="12" cy="12" r="10" /><path d="M12 8v4" /><path d="M12 16h.01" />
                        </svg>
                        <span>
                            <strong style={{ display: 'block', fontSize: 13 }}>{w.title}</strong>
                            <span style={{ fontSize: 12, lineHeight: 1.55 }}>{w.message}</span>
                            <span style={{ display: 'block', fontSize: 11, marginTop: 4, opacity: 0.8 }}>
                                Baris manfaat: #{w.benefit_ids.join(', #')}
                            </span>
                        </span>
                    </div>
                ))}
            </div>

            {warnings.length > 3 && (
                <button type="button" className="btn btn-sm btn-ghost" style={{ marginTop: 10, padding: '4px 0' }} onClick={() => setExpanded((v) => !v)}>
                    {expanded ? 'Sembunyikan' : `Lihat ${warnings.length - 3} peringatan lainnya`}
                </button>
            )}
        </div>
    );
}

/**
 * The discounting assumptions every present value on this page depends on.
 * Shown rather than implied, because a TEV means nothing without the rate and
 * base year it was computed under.
 */
function ValuationSettingsPanel({ project, settings, eopValueBases, canManage }) {
    const [editing, setEditing] = useState(false);

    const { data, setData, put, processing, errors } = useForm({
        base_year: settings.base_year,
        discount_rate: settings.discount_rate,
        analysis_period: settings.analysis_period,
        currency: settings.currency,
        start_year: settings.start_year ?? '',
        end_year: settings.end_year ?? '',
        eop_value_basis: settings.eop_value_basis,
    });

    function submit(e) {
        e.preventDefault();
        put(route('admin.projects.settings.update', project.id), {
            preserveScroll: true,
            onSuccess: () => setEditing(false),
        });
    }

    const summary = [
        { label: 'Tahun Dasar', value: settings.base_year },
        { label: 'Discount Rate', value: `${formatRupiah(settings.discount_rate, 2)}%` },
        { label: 'Periode Analisis', value: `${settings.analysis_period} tahun` },
        { label: 'Mata Uang', value: settings.currency },
        { label: 'Dasar Nilai EOP', value: eopValueBases[settings.eop_value_basis] || settings.eop_value_basis },
        {
            label: 'Rentang Tahun',
            value: settings.start_year || settings.end_year
                ? `${settings.start_year ?? '—'} – ${settings.end_year ?? '—'}`
                : '—',
        },
    ];

    return (
        <div className="card" style={{ marginBottom: 24 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12, flexWrap: 'wrap', marginBottom: 14 }}>
                <div>
                    <h3 style={{ fontSize: 15, fontWeight: 700 }}>Asumsi Valuasi</h3>
                    <p style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 2 }}>
                        PV = Nilai / (1 + r)ⁿ · Semua manfaat dan biaya didiskontokan ke tahun dasar.
                    </p>
                </div>
                <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
                    {!settings.is_configured && (
                        <span className="badge badge-warning">Memakai nilai bawaan</span>
                    )}
                    {canManage && (
                        <button type="button" className="btn btn-sm btn-outline" onClick={() => setEditing((v) => !v)}>
                            {editing ? 'Tutup' : 'Ubah Asumsi'}
                        </button>
                    )}
                </div>
            </div>

            {editing ? (
                <form onSubmit={submit}>
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(190px, 1fr))', gap: '0 16px' }}>
                        <FormField field={{ name: 'base_year', label: 'Tahun Dasar', type: 'number', step: '1', required: true, hint: 'Tahun tempat semua nilai didiskontokan (n = 0).' }} value={data.base_year} onChange={(v) => setData('base_year', v)} error={errors.base_year} />
                        <FormField field={{ name: 'discount_rate', label: 'Discount Rate', type: 'number', required: true, suffix: '%', allowNegative: true, hint: 'Contoh: 6 untuk 6% per tahun.' }} value={data.discount_rate} onChange={(v) => setData('discount_rate', v)} error={errors.discount_rate} />
                        <FormField field={{ name: 'analysis_period', label: 'Periode Analisis', type: 'number', step: '1', required: true, suffix: 'tahun' }} value={data.analysis_period} onChange={(v) => setData('analysis_period', v)} error={errors.analysis_period} />
                        <FormField field={{ name: 'currency', label: 'Mata Uang', type: 'text', required: true }} value={data.currency} onChange={(v) => setData('currency', v)} error={errors.currency} />
                        <FormField field={{ name: 'start_year', label: 'Tahun Mulai', type: 'number', step: '1' }} value={data.start_year} onChange={(v) => setData('start_year', v)} error={errors.start_year} />
                        <FormField field={{ name: 'end_year', label: 'Tahun Akhir', type: 'number', step: '1' }} value={data.end_year} onChange={(v) => setData('end_year', v)} error={errors.end_year} />
                        <FormField field={{ name: 'eop_value_basis', label: 'Dasar Nilai EOP', type: 'select', required: true, options: Object.entries(eopValueBases), hint: 'Menentukan nilai EOP mana yang dipakai sebagai manfaat.' }} value={data.eop_value_basis} onChange={(v) => setData('eop_value_basis', v)} error={errors.eop_value_basis} />
                    </div>
                    <div style={{ display: 'flex', gap: 10, paddingTop: 12, borderTop: '1px solid var(--border-light)' }}>
                        <button type="submit" className="btn btn-sm btn-primary" disabled={processing}>
                            {processing ? 'Menyimpan…' : 'Simpan & Hitung Ulang'}
                        </button>
                        <button type="button" className="btn btn-sm btn-ghost" onClick={() => setEditing(false)}>Batal</button>
                    </div>
                </form>
            ) : (
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))', gap: 14 }}>
                    {summary.map((row) => (
                        <div key={row.label}>
                            <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>{row.label}</div>
                            <div style={{ fontSize: 14, fontWeight: 700, marginTop: 2 }}>{row.value}</div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}

export default function Show({
    project, benefits, costs, ecosystemIndices = [], modules = [],
    serviceCategories = {}, valuationSettings, eopValueBases = {},
    doubleCountingWarnings = [],
}) {
    const { auth } = usePage().props;
    const canManage = auth.user.isAdmin;
    // null means undefined, not zero — a project with benefits and no recorded
    // cost has no ratio, and printing 0 would read as "no benefit at all".
    const bcrDefined = project.bcr !== null && project.bcr !== undefined;
    const bcr = toNumber(project.bcr);
    // Marks the rows named by the warnings above, so the panel and the table
    // point at the same benefits without the reader matching ids by eye.
    const flaggedBenefitIds = new Set(doubleCountingWarnings.flatMap((w) => w.benefit_ids));

    function calculateTev(e) {
        e.preventDefault();
        router.post(route('admin.projects.calculateTEV', project.id));
    }

    return (
        <AdminLayout title={project.name} subtitle={`Detail Proyek ${project.code}`}>
            <Head title={project.name} />

            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24 }}>
                <Link href={route('admin.projects.index')} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13 }}>← Kembali ke Daftar Proyek</Link>
                {canManage && (
                    <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
                        <Link href={route('admin.assumptions.index', project.id)} className="btn btn-sm btn-outline">Uji Asumsi</Link>
                        <Link href={route('admin.stakeholder-validations.index', project.id)} className="btn btn-sm btn-outline">Validasi Stakeholder</Link>
                        <form onSubmit={calculateTev} style={{ display: 'inline' }}>
                            <button type="submit" className="btn btn-sm btn-secondary">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><rect x="4" y="2" width="16" height="20" rx="2" /><path d="M8 6h8M8 10h.01M12 10h.01M16 10h.01M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01" /></svg>
                                Hitung TEV
                            </button>
                        </form>
                        <Link href={route('admin.projects.edit', project.id)} className="btn btn-sm btn-outline">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M12 20h9" /><path d="M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4z" /></svg>
                            Edit
                        </Link>
                    </div>
                )}
            </div>

            {project.ecosystem_object_type && (
                <div style={{ marginBottom: 20 }}>
                    <span style={{
                        display: 'inline-flex', alignItems: 'center', gap: 6,
                        padding: '4px 12px', borderRadius: 999, fontSize: 12, fontWeight: 600,
                        background: 'var(--primary-light, #eef2ff)', color: 'var(--primary)',
                    }}>
                        Objek Ekosistem: {ecosystemObjectTypeLabel(project.ecosystem_object_type)}
                    </span>
                </div>
            )}

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: 16, marginBottom: 28 }}>
                <div className="stat-card" style={{ textAlign: 'center' }}>
                    <div className="stat-label">TEV</div>
                    <div className="stat-value" style={{ fontSize: 22, color: 'var(--primary)' }}>Rp{formatRupiah(project.tev, 0)}</div>
                    <div style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 4 }}>Total Economic Value</div>
                </div>
                <div className="stat-card" style={{ textAlign: 'center' }}>
                    <div className="stat-label">Total Manfaat</div>
                    <div className="stat-value" style={{ fontSize: 22, color: 'var(--success)' }}>Rp{formatRupiah(project.total_benefits, 0)}</div>
                    <div style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 4 }}>Present Value (PV)</div>
                </div>
                <div className="stat-card" style={{ textAlign: 'center' }}>
                    <div className="stat-label">Total Biaya</div>
                    <div className="stat-value" style={{ fontSize: 22, color: 'var(--danger)' }}>Rp{formatRupiah(project.total_costs, 0)}</div>
                    <div style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 4 }}>Present Value (PV)</div>
                </div>
                <div className="stat-card" style={{ textAlign: 'center' }}>
                    <div className="stat-label">BCR</div>
                    <div
                        className="stat-value"
                        style={{ fontSize: 22, color: bcrDefined ? (bcr >= 1 ? 'var(--success)' : 'var(--danger)') : 'var(--text-muted)' }}
                        title={bcrDefined ? undefined : 'Belum ada biaya tercatat, sehingga rasio tidak terdefinisi'}
                    >
                        {bcrDefined ? formatRupiah(bcr, 2) : '—'}
                    </div>
                    <div style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 4 }}>
                        {bcrDefined ? 'Benefit Cost Ratio' : 'Belum ada biaya'}
                    </div>
                </div>
            </div>

            <DoubleCountingPanel warnings={doubleCountingWarnings} />

            {valuationSettings && (
                <ValuationSettingsPanel
                    project={project}
                    settings={valuationSettings}
                    eopValueBases={eopValueBases}
                    canManage={canManage}
                />
            )}

            <div className="card" style={{ marginBottom: 24 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16, gap: 12, flexWrap: 'wrap' }}>
                    <h3 style={{ fontSize: 15, fontWeight: 700 }}>Status Modul</h3>
                    <Link href={route('admin.modules.index', project.id)} className="btn btn-sm btn-ghost" style={{ border: '1px solid var(--border)' }}>
                        Kelola Modul →
                    </Link>
                </div>
                {modules.length ? (
                    /* auto-fit, not auto-fill: empty tracks collapse so the tiles stretch
                       to fill the row instead of leaving a gap on the right. */
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(215px, 1fr))', gap: 12, alignItems: 'stretch' }}>
                        {modules.map((mod) => (
                            <ModuleStatusCard key={mod.code} mod={mod} categoryLabels={serviceCategories} />
                        ))}
                    </div>
                ) : (
                    <p style={{ textAlign: 'center', color: 'var(--text-muted)', padding: 20 }}>
                        Belum ada modul yang ditampilkan pada detail proyek.
                    </p>
                )}
            </div>

            <EcosystemValuationSection ecosystemIndices={ecosystemIndices} />

            {/* Stacked, not side by side: both tables carry six columns of currency
                and action buttons, so each needs the full container width. */}
            <div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: 16, marginBottom: 20 }}>
                <div className="card">
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
                        <h3 style={{ fontSize: 15, fontWeight: 700 }}>Manfaat (Benefits)</h3>
                        {canManage && <Link href={route('admin.benefits.create', project.id)} className="btn btn-sm btn-primary">+ Tambah</Link>}
                    </div>
                    {benefits.data.length ? (
                        <>
                            <div className="table-wrapper">
                                <table className="data-table">
                                    {/* Fixed widths on the narrow columns so the full-width table
                                        lets "Jenis Manfaat" absorb the slack instead of stretching "No". */}
                                    <thead><tr><th style={{ width: 56 }}>No</th><th>Jenis Manfaat</th><th style={{ width: 120 }}>Metode</th><th style={{ width: 210, textAlign: 'right' }}>Nilai (PV)</th><th style={{ width: 120, textAlign: 'right' }}>Kontribusi</th><th style={{ width: 150 }}>Aksi</th></tr></thead>
                                    <tbody>
                                        {benefits.data.map((b, i) => (
                                            <tr key={b.id}>
                                                <td style={{ fontSize: 13 }}>{(benefits.from || 1) + i}</td>
                                                <td title={b.description || ''}>
                                                    <div style={{ fontSize: 13, fontWeight: 600 }}>{(b.subcategory || b.description || '-').replace(/_/g, ' ')}</div>
                                                    <div style={{ display: 'flex', gap: 4, flexWrap: 'wrap', marginTop: 4 }}>
                                                        <span className={`badge ${b.category === 'direct_use' ? 'badge-primary' : b.category === 'indirect_use' ? 'badge-success' : 'badge-purple'}`}>{b.category.replace('_', ' ')}</span>
                                                        {b.data_status === 'draft' && <span className="badge badge-warning">Draft</span>}
                                                        {flaggedBenefitIds.has(b.id) && (
                                                            <span className="badge badge-danger" title="Berpotensi double counting — lihat peringatan di atas">⚠ Tumpang tindih</span>
                                                        )}
                                                    </div>
                                                </td>
                                                <td><span className="badge badge-info">{b.method_used}</span></td>
                                                <td style={{ textAlign: 'right', fontWeight: 700 }}>
                                                    Rp{formatRupiah(b.pv_value)}
                                                    <div style={{ fontSize: 10.5, fontWeight: 400, color: 'var(--text-muted)' }}>
                                                        nominal Rp{formatRupiah(b.value)}{b.period_year ? ` · ${b.period_year}` : ''}
                                                    </div>
                                                </td>
                                                <td style={{ textAlign: 'right', fontSize: 13 }}>{formatRupiah(pct(b.pv_value, project.total_benefits), 1)}%</td>
                                                <td>
                                                    {canManage && (
                                                        <div style={{ display: 'flex', gap: 4 }}>
                                                            <Link href={route('admin.benefits.edit', [project.id, b.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                                            <ConfirmDeleteButton href={route('admin.benefits.destroy', [project.id, b.id])} />
                                                        </div>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                    <tfoot>
                                        <tr style={{ background: 'var(--surface-alt)' }}>
                                            <td colSpan={3} style={{ fontWeight: 700, borderBottom: 'none' }}>TOTAL</td>
                                            <td style={{ textAlign: 'right', fontWeight: 700, borderBottom: 'none' }}>Rp{formatRupiah(project.total_benefits)}</td>
                                            <td style={{ textAlign: 'right', fontWeight: 700, borderBottom: 'none' }}>100%</td>
                                            <td style={{ borderBottom: 'none' }} />
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <Pagination paginator={benefits} />
                        </>
                    ) : (
                        <p style={{ textAlign: 'center', color: 'var(--text-muted)', padding: 20 }}>Belum ada data manfaat.</p>
                    )}
                </div>

                <div className="card">
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
                        <h3 style={{ fontSize: 15, fontWeight: 700 }}>Biaya (Costs)</h3>
                        {canManage && <Link href={route('admin.costs.create', project.id)} className="btn btn-sm btn-primary">+ Tambah</Link>}
                    </div>
                    {costs.data.length ? (
                        <>
                            <div className="table-wrapper">
                                <table className="data-table">
                                    <thead><tr><th style={{ width: 56 }}>No</th><th>Jenis Biaya</th><th style={{ width: 210, textAlign: 'right' }}>Nilai (PV)</th><th style={{ width: 120, textAlign: 'right' }}>Persentase</th><th style={{ width: 150 }}>Aksi</th></tr></thead>
                                    <tbody>
                                        {costs.data.map((c, i) => (
                                            <tr key={c.id}>
                                                <td style={{ fontSize: 13 }}>{(costs.from || 1) + i}</td>
                                                <td title={c.description || ''}>
                                                    <div style={{ fontSize: 13, fontWeight: 600 }}>{(c.subcategory || c.description || '-').replace(/_/g, ' ')}</div>
                                                    <span className={`badge ${c.category === 'direct_cost' ? 'badge-warning' : 'badge-danger'}`} style={{ marginTop: 4 }}>{c.category.replace('_', ' ')}</span>
                                                </td>
                                                <td style={{ textAlign: 'right', fontWeight: 700 }}>
                                                    Rp{formatRupiah(c.pv_value)}
                                                    <div style={{ fontSize: 10.5, fontWeight: 400, color: 'var(--text-muted)' }}>
                                                        nominal Rp{formatRupiah(c.value)}{c.year_applied ? ` · ${c.year_applied}` : ''}
                                                    </div>
                                                </td>
                                                <td style={{ textAlign: 'right', fontSize: 13 }}>{formatRupiah(pct(c.pv_value, project.total_costs), 1)}%</td>
                                                <td>
                                                    {canManage && (
                                                        <div style={{ display: 'flex', gap: 4 }}>
                                                            <Link href={route('admin.costs.edit', [project.id, c.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                                            <ConfirmDeleteButton href={route('admin.costs.destroy', [project.id, c.id])} />
                                                        </div>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                    <tfoot>
                                        <tr style={{ background: 'var(--surface-alt)' }}>
                                            <td colSpan={2} style={{ fontWeight: 700, borderBottom: 'none' }}>TOTAL</td>
                                            <td style={{ textAlign: 'right', fontWeight: 700, borderBottom: 'none' }}>Rp{formatRupiah(project.total_costs)}</td>
                                            <td style={{ textAlign: 'right', fontWeight: 700, borderBottom: 'none' }}>100%</td>
                                            <td style={{ borderBottom: 'none' }} />
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <Pagination paginator={costs} />
                        </>
                    ) : (
                        <p style={{ textAlign: 'center', color: 'var(--text-muted)', padding: 20 }}>Belum ada data biaya.</p>
                    )}
                </div>
            </div>

            <div className="alert alert-info">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="12" cy="12" r="10" /><path d="M12 16v-4" /><path d="M12 8h.01" /></svg>
                <span>
                    Total Manfaat dan Total Biaya adalah Present Value pada tahun dasar {valuationSettings?.base_year},
                    discount rate {formatRupiah(valuationSettings?.discount_rate, 2)}% per tahun.
                    Pos tanpa tahun dihitung pada nilai nominalnya (faktor diskonto = 1).
                </span>
            </div>

            {/* Reference material, so it sits below the project's own numbers.
                Full width and stacked, matching the benefit/cost tables above. */}
            <div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: 16, marginBottom: 20 }}>
                <div className="card">
                    <h3 style={{ fontSize: 15, fontWeight: 700, marginBottom: 16 }}>Kerangka Valuasi</h3>
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(125px, 1fr))', gap: 12 }}>
                        {VALUATION_FRAMEWORK.map((f) => (
                            <div key={f.key} style={{ border: '1px solid var(--border)', borderTop: `3px solid ${f.color}`, borderRadius: 8, padding: 14, display: 'flex', flexDirection: 'column' }}>
                                <span style={{ width: 30, height: 30, borderRadius: 8, background: `${f.color}1A`, color: f.color, display: 'flex', alignItems: 'center', justifyContent: 'center', marginBottom: 8 }}>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">{f.icon}</svg>
                                </span>
                                <div style={{ fontWeight: 700, fontSize: 13 }}>{f.title}</div>
                                <div style={{ fontSize: 11, color: 'var(--text-muted)', marginBottom: 8 }}>{f.subtitle}</div>
                                <div style={{ display: 'flex', flexDirection: 'column', gap: 5, flex: 1 }}>
                                    {f.items.map((item) => (
                                        <div key={item} style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 12, color: 'var(--text-secondary)' }}>
                                            <span style={{ width: 5, height: 5, borderRadius: '50%', background: f.color, flexShrink: 0 }} />
                                            {item}
                                        </div>
                                    ))}
                                </div>
                                <div style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 10, paddingTop: 8, borderTop: '1px solid var(--border)' }}>{f.caption}</div>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="card">
                    <h3 style={{ fontSize: 15, fontWeight: 700, marginBottom: 16 }}>Ringkasan Metode</h3>
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(260px, 1fr))', gap: 12, alignItems: 'start' }}>
                        {METHOD_SUMMARY.map((m) => (
                            <div key={m.code} style={{ border: '1px solid var(--border)', borderRadius: 8, padding: 12 }}>
                                <span className={`badge ${m.badge}`} style={{ marginBottom: 8 }}>{m.code}</span>
                                <div style={{ fontFamily: 'ui-monospace, SFMono-Regular, Menlo, monospace', fontSize: 11.5, whiteSpace: 'pre-line', color: 'var(--text-secondary)', background: 'var(--primary-50)', borderRadius: 6, padding: '8px 10px', marginTop: 6, lineHeight: 1.6 }}>
                                    {m.formula}
                                </div>
                                <div style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 8 }}>{m.desc}</div>
                            </div>
                        ))}
                        <div style={{ border: '1px dashed var(--border)', borderRadius: 8, padding: 12 }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 8, fontWeight: 700, fontSize: 12.5 }}>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="12" cy="12" r="3" /><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.65 1.65 0 004.6 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06A1.65 1.65 0 009 4.6a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" /></svg>
                                Metode Lanjutan (opsional): HPM, ABM, CE
                            </div>
                            <div style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 6 }}>Digunakan untuk analisis lanjutan sesuai kebutuhan proyek.</div>
                        </div>
                    </div>
                </div>

                <div className="card">
                    <h3 style={{ fontSize: 15, fontWeight: 700, marginBottom: 4 }}>Variabel Kunci</h3>
                    <p style={{ fontSize: 11, color: 'var(--text-muted)', marginBottom: 14 }}>Variabel input yang umum dipakai lintas metode valuasi.</p>
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(230px, 1fr))', gap: 12, alignItems: 'start' }}>
                        {KEY_VARIABLES.map((v) => (
                            <div key={v.label} style={{ display: 'flex', gap: 8, alignItems: 'flex-start' }}>
                                <span style={{ width: 14, height: 14, borderRadius: 4, background: `${v.color}1A`, border: `1px solid ${v.color}`, flexShrink: 0, marginTop: 2 }} />
                                <div style={{ fontSize: 12 }}>
                                    <strong>{v.label}</strong> — <span style={{ color: 'var(--text-muted)' }}>{v.desc}</span>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

        </AdminLayout>
    );
}
