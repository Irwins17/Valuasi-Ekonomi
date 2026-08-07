import { Head, Link } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import RevealOnScroll from '../../Components/effects/RevealOnScroll';
import CountUp from '../../Components/effects/CountUp';
import ProvinceMap from '../../Components/map/ProvinceMap';
import { toNumber } from '../../lib/format';

const FEATURES = [
    { icon: '', title: 'Dashboard Komprehensif', desc: 'Lihat breakdown lengkap manfaat, biaya, dan metodologi valuasi dari setiap proyek.', color: '#6366f1' },
    { icon: '📍', title: 'Peta Valuasi Interaktif', desc: 'Visualisasi lokasi geografis dari semua proyek valuasi dengan data real-time.', color: '#06d6a0' },
    { icon: '', title: 'Edukasi & Transparansi', desc: 'Pelajari metodologi TCM, CVM, dan EOP yang digunakan untuk menghasilkan angka-angka tersebut.', color: '#f72585' },
];

export default function Landing({ publishedProjects, totalTEV, totalBenefits, avgBCR, mapProjects }) {
    return (
        <GuestLayout>
            <Head title="Valuasi Ekonomi — Platform Transparansi Data" />

            {/* Hero */}
            <section
                className="gradient-animated"
                style={{
                    background: 'linear-gradient(135deg,#1e1b4b 0%,#312e81 30%,#4f46e5 60%,#06d6a0 100%)',
                    backgroundSize: '300% 300%',
                    padding: '100px 0 80px',
                    position: 'relative',
                    overflow: 'hidden',
                }}
            >
                <div style={{ position: 'absolute', inset: 0, background: "url('data:image/svg+xml,%3Csvg width=%2260%22 height=%2260%22 viewBox=%220 0 60 60%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22none%22 fill-rule=%22evenodd%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%220.05%22%3E%3Ccircle cx=%2230%22 cy=%2230%22 r=%221.5%22/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')" }}></div>
                <div style={{ maxWidth: 1280, margin: '0 auto', padding: '0 24px', position: 'relative', zIndex: 1 }}>
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 60, alignItems: 'center' }}>
                        <div className="animate-fade-left">
                            <div className="badge" style={{ background: 'rgba(255,255,255,0.15)', color: '#fff', backdropFilter: 'blur(10px)', marginBottom: 20, fontSize: 13, padding: '6px 14px' }}>
                                🌿 Platform Valuasi Ekonomi Terpercaya
                            </div>
                            <h1 style={{ fontSize: 52, fontWeight: 900, color: '#fff', lineHeight: 1.15, marginBottom: 20 }}>
                                Transparansi<br />
                                <span style={{ background: 'linear-gradient(90deg,#06d6a0,#3b82f6)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent' }}>Valuasi Ekonomi</span>
                            </h1>
                            <p style={{ fontSize: 18, color: 'rgba(255,255,255,0.8)', lineHeight: 1.7, marginBottom: 32, maxWidth: 500 }}>
                                Mengukur dampak ekonomi dari proyek keberlanjutan dengan metodologi ilmiah — TCM, CVM, dan EOP.
                            </p>
                            <div style={{ display: 'flex', gap: 12, flexWrap: 'wrap' }}>
                                <Link href={route('public.dashboard')} className="btn btn-secondary btn-lg">📊 Lihat Dashboard</Link>
                                <Link href={route('public.glossary')} className="btn btn-outline-white btn-lg">Pelajari Metodologi</Link>
                            </div>
                        </div>
                        <div className="animate-fade-right">
                            <div style={{ background: 'rgba(255,255,255,0.08)', backdropFilter: 'blur(20px)', border: '1px solid rgba(255,255,255,0.15)', borderRadius: 20, padding: 32, position: 'relative' }}>
                                <div style={{ position: 'absolute', top: -20, right: -20, width: 80, height: 80, background: 'linear-gradient(135deg,var(--secondary),var(--primary))', borderRadius: '50%', opacity: 0.3, filter: 'blur(30px)' }}></div>
                                <div style={{ textAlign: 'center', marginBottom: 24 }}>
                                    <div style={{ fontSize: 14, color: 'rgba(255,255,255,0.6)', marginBottom: 8 }}>Total Economic Value</div>
                                    <CountUp
                                        value={toNumber(totalTEV) / 1e9}
                                        decimals={1}
                                        prefix="Rp "
                                        suffix=" T"
                                        style={{ fontSize: 42, fontWeight: 900, color: '#fff' }}
                                    />
                                </div>
                                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                                    <div style={{ background: 'rgba(255,255,255,0.08)', borderRadius: 12, padding: 16, textAlign: 'center' }}>
                                        <CountUp value={toNumber(totalBenefits) / 1e9} decimals={1} prefix="Rp " suffix="T" style={{ fontSize: 28, fontWeight: 800, color: '#06d6a0' }} />
                                        <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.6)', marginTop: 4 }}>Total Manfaat</div>
                                    </div>
                                    <div style={{ background: 'rgba(255,255,255,0.08)', borderRadius: 12, padding: 16, textAlign: 'center' }}>
                                        <CountUp value={toNumber(avgBCR)} decimals={2} style={{ fontSize: 28, fontWeight: 800, color: '#818cf8' }} />
                                        <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.6)', marginTop: 4 }}>Rata-rata BCR</div>
                                    </div>
                                </div>
                                <div style={{ marginTop: 16, background: 'rgba(255,255,255,0.08)', borderRadius: 12, padding: 16, textAlign: 'center' }}>
                                    <CountUp value={toNumber(publishedProjects)} decimals={0} style={{ fontSize: 28, fontWeight: 800, color: '#f472b6' }} />
                                    <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.6)', marginTop: 4 }}>Proyek Dipublikasikan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Map */}
            <RevealOnScroll as="section" style={{ padding: '80px 0', background: 'var(--surface)' }}>
                <div style={{ maxWidth: 1280, margin: '0 auto', padding: '0 24px' }}>
                    <div style={{ textAlign: 'center', marginBottom: 40 }}>
                        <div className="badge badge-primary" style={{ marginBottom: 12 }}>📍 Lokasi Proyek</div>
                        <h2 style={{ fontSize: 36, fontWeight: 800, marginBottom: 12 }}>Peta Valuasi Interaktif</h2>
                        <p style={{ color: 'var(--text-secondary)', fontSize: 16, maxWidth: 600, margin: '0 auto' }}>Visualisasi lokasi geografis dari semua proyek valuasi ekonomi yang telah dipublikasikan.</p>
                    </div>
                    <div style={{ borderRadius: 'var(--radius-lg)', overflow: 'hidden', boxShadow: 'var(--shadow-xl)', border: '1px solid var(--border)' }}>
                        <ProvinceMap projects={mapProjects} height={460} />
                    </div>
                </div>
            </RevealOnScroll>

            {/* Features */}
            <RevealOnScroll as="section" style={{ padding: '80px 0', background: 'var(--surface-alt)' }}>
                <div style={{ maxWidth: 1280, margin: '0 auto', padding: '0 24px' }}>
                    <div style={{ textAlign: 'center', marginBottom: 48 }}>
                        <h2 style={{ fontSize: 36, fontWeight: 800, marginBottom: 12 }}>Fitur Utama</h2>
                        <p style={{ color: 'var(--text-secondary)', fontSize: 16 }}>Sistem lengkap untuk transparansi dan akuntabilitas data valuasi</p>
                    </div>
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: 24 }} className="stagger">
                        {FEATURES.map((f) => (
                            <div className="card" key={f.title} style={{ textAlign: 'center', padding: '36px 28px' }}>
                                <div style={{ width: 64, height: 64, borderRadius: 16, background: `${f.color}15`, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 28, margin: '0 auto 20px' }}>{f.icon}</div>
                                <h3 style={{ fontSize: 18, fontWeight: 700, marginBottom: 10 }}>{f.title}</h3>
                                <p style={{ fontSize: 14, color: 'var(--text-secondary)', lineHeight: 1.7 }}>{f.desc}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </RevealOnScroll>

            {/* CTA */}
            <RevealOnScroll as="section" style={{ padding: '80px 0', background: 'linear-gradient(135deg,#312e81,#4f46e5)', position: 'relative', overflow: 'hidden' }}>
                <div style={{ position: 'absolute', top: 0, right: 0, width: 400, height: 400, background: 'radial-gradient(circle,rgba(6,214,160,0.2) 0%,transparent 70%)' }}></div>
                <div style={{ maxWidth: 800, margin: '0 auto', padding: '0 24px', textAlign: 'center', position: 'relative', zIndex: 1 }}>
                    <h2 style={{ fontSize: 36, fontWeight: 800, color: '#fff', marginBottom: 16 }}>Siap Mengeksplorasi Data?</h2>
                    <p style={{ fontSize: 18, color: 'rgba(255,255,255,0.8)', marginBottom: 32 }}>Akses dashboard publik kami untuk melihat semua proyek valuasi ekonomi yang telah dipublikasikan.</p>
                    <Link href={route('public.dashboard')} className="btn btn-secondary btn-lg">🔍 Buka Dashboard Publik</Link>
                </div>
            </RevealOnScroll>
        </GuestLayout>
    );
}
