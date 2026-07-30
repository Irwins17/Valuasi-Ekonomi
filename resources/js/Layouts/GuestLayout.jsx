import { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    const { props, url: currentPath } = usePage();
    const { auth } = props;
    const [scrolled, setScrolled] = useState(false);

    useEffect(() => {
        function onScroll() {
            setScrolled(window.scrollY > 50);
        }
        window.addEventListener('scroll', onScroll);
        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    function isActive(path) {
        return currentPath === path || currentPath.startsWith(path + '?');
    }

    return (
        <div className="page-enter">
            <nav id="main-nav" className={scrolled ? 'scrolled' : ''} style={{ position: 'fixed', top: 0, left: 0, right: 0, zIndex: 100, transition: 'all .3s ease' }}>
                <div style={{ maxWidth: 1280, margin: '0 auto', padding: '16px 24px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <Link href={route('landing')} style={{ display: 'flex', alignItems: 'center', gap: 10, textDecoration: 'none' }}>
                        <span style={{ fontWeight: 700, fontSize: 18, color: 'var(--text)' }}>Valuasi Ekonomi</span>
                    </Link>

                    <div style={{ display: 'flex', alignItems: 'center', gap: 32 }} className="hide-mobile">
                        <Link href={route('landing')} className={`nav-link ${isActive('/') ? 'active' : ''}`} style={{ fontWeight: 500, fontSize: 14, color: 'var(--text-secondary)', textDecoration: 'none', position: 'relative' }}>Beranda</Link>
                        <Link href={route('public.dashboard')} className={`nav-link ${isActive('/dashboard-publik') ? 'active' : ''}`} style={{ fontWeight: 500, fontSize: 14, color: 'var(--text-secondary)', textDecoration: 'none' }}>Dashboard</Link>
                        <Link href={route('public.glossary')} className={`nav-link ${isActive('/glossary') ? 'active' : ''}`} style={{ fontWeight: 500, fontSize: 14, color: 'var(--text-secondary)', textDecoration: 'none' }}>Edukasi</Link>
                    </div>

                    <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                        {auth.user ? (
                            <>
                                <span style={{ fontSize: 14, color: 'var(--text-secondary)' }}>{auth.user.name}</span>
                                <Link href={route('dashboard')} className="btn btn-sm btn-primary">Dashboard</Link>
                            </>
                        ) : (
                            <Link href={route('login')} className="btn btn-sm btn-primary">Masuk</Link>
                        )}
                    </div>
                </div>
            </nav>

            <main style={{ marginTop: 74 }}>{children}</main>

            <footer style={{ background: '#0f172a', color: '#fff', padding: '60px 0 0' }}>
                <div style={{ maxWidth: 1280, margin: '0 auto', padding: '0 24px' }}>
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(220px,1fr))', gap: 40, marginBottom: 40 }}>
                        <div>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 16 }}>
                                <span style={{ fontWeight: 700, fontSize: 16 }}>Valuasi Ekonomi</span>
                            </div>
                            <p style={{ color: '#94a3b8', fontSize: 14, lineHeight: 1.7 }}>Platform transparansi data valuasi ekonomi untuk kebijakan berkelanjutan dan investasi publik.</p>
                        </div>
                        <div>
                            <h4 style={{ fontWeight: 700, marginBottom: 16, fontSize: 14, textTransform: 'uppercase', letterSpacing: '0.05em', color: '#64748b' }}>Navigasi</h4>
                            <ul style={{ listStyle: 'none', display: 'flex', flexDirection: 'column', gap: 10 }}>
                                <li><Link href={route('landing')} style={{ color: '#94a3b8', textDecoration: 'none', fontSize: 14 }}>Beranda</Link></li>
                                <li><Link href={route('public.dashboard')} style={{ color: '#94a3b8', textDecoration: 'none', fontSize: 14 }}>Dashboard Publik</Link></li>
                                <li><Link href={route('public.glossary')} style={{ color: '#94a3b8', textDecoration: 'none', fontSize: 14 }}>Glosarium & Edukasi</Link></li>
                            </ul>
                        </div>
                        <div>
                            <h4 style={{ fontWeight: 700, marginBottom: 16, fontSize: 14, textTransform: 'uppercase', letterSpacing: '0.05em', color: '#64748b' }}>Metodologi</h4>
                            <ul style={{ listStyle: 'none', display: 'flex', flexDirection: 'column', gap: 10 }}>
                                <li><span style={{ color: '#94a3b8', fontSize: 14 }}>Travel Cost Method (TCM)</span></li>
                                <li><span style={{ color: '#94a3b8', fontSize: 14 }}>Contingent Valuation (CVM)</span></li>
                                <li><span style={{ color: '#94a3b8', fontSize: 14 }}>Effect on Production (EOP)</span></li>
                            </ul>
                        </div>
                        <div>
                            <h4 style={{ fontWeight: 700, marginBottom: 16, fontSize: 14, textTransform: 'uppercase', letterSpacing: '0.05em', color: '#64748b' }}>Kontak</h4>
                            <p style={{ color: '#94a3b8', fontSize: 14 }}>Email: info@valuasi.local</p>
                            <p style={{ color: '#94a3b8', fontSize: 14, marginTop: 8 }}>Telp: (021) 123-4567</p>
                        </div>
                    </div>
                    <div style={{ borderTop: '1px solid #1e293b', padding: '24px 0', textAlign: 'center' }}>
                        <p style={{ color: '#64748b', fontSize: 13 }}>&copy; {new Date().getFullYear()} Valuasi Ekonomi. Semua hak dilindungi.</p>
                    </div>
                </div>
            </footer>

            <style>{`
                #main-nav { background: rgba(255,255,255,0.85); backdrop-filter: blur(20px); border-bottom: 1px solid var(--border-light); }
                #main-nav.scrolled { background: rgba(255,255,255,0.95); box-shadow: var(--shadow-md); }
                .nav-link:hover { color: var(--primary) !important; }
                .nav-link.active { color: var(--primary) !important; }
                .nav-link.active::after { content:''; position:absolute; bottom:-4px; left:0; right:0; height:2px; background:var(--primary); border-radius:2px; }
            `}</style>
        </div>
    );
}
