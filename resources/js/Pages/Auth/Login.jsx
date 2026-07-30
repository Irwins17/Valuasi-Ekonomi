import { useState } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';

export default function Login() {
    const [dark, setDark] = useState(() => localStorage.getItem('dcLoginDark') === '1');
    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    function toggleDark() {
        const next = !dark;
        setDark(next);
        localStorage.setItem('dcLoginDark', next ? '1' : '0');
    }

    function submit(e) {
        e.preventDefault();
        post(route('login.post'));
    }

    return (
        <>
            <Head title="Sign in to Valek" />

            <div className={`dc-login ${dark ? 'dark' : ''}`} id="dcLogin">
                <div className="dc-card animate-scale">
                    <div className="dc-art">
                        <img src="/images/pemandangan.png" alt="Ilustrasi danau dengan perahu dan pegunungan" />
                    </div>

                    <div className="dc-panel">
                        <div className="dc-brand">
                            <div className="dc-brand-name">
                                <span>Valuasi Ekonomi</span>
                                <span className="dc-brand-dot"></span>
                            </div>
                            <button type="button" className="dc-toggle" aria-label="Ganti mode gelap" onClick={toggleDark}>
                                <span className="dc-toggle-knob"></span>
                            </button>
                        </div>

                        <div className="dc-rule" style={{ margin: '18px 0 22px' }}></div>

                        <h1 className="dc-title">Sign in to Valek</h1>
                        <p className="dc-subtitle">Portal Administrasi Data Valuasi Ekonomi Sumber Daya Alam</p>

                        <div className="dc-rule" style={{ margin: '20px 0 24px' }}></div>

                        {errors.email && (
                            <div className="dc-alert" style={{ marginBottom: 20, marginTop: 0 }}>
                                {errors.email}
                            </div>
                        )}

                        <form onSubmit={submit}>
                            <div className="dc-fields">
                                <input
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    required
                                    autoFocus
                                    className="dc-input"
                                    placeholder="Alamat email"
                                    aria-label="Email"
                                />
                                <div className="dc-pw-wrap">
                                    <input
                                        type={showPassword ? 'text' : 'password'}
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        required
                                        className="dc-input"
                                        placeholder="Password"
                                        aria-label="Password"
                                    />
                                    <button
                                        type="button"
                                        className="dc-pw-toggle"
                                        aria-label={showPassword ? 'Sembunyikan password' : 'Tampilkan password'}
                                        onClick={() => setShowPassword((v) => !v)}
                                    >
                                        {showPassword ? (
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a21.8 21.8 0 015.06-6.06M9.9 4.24A10.4 10.4 0 0112 4c7 0 11 8 11 8a21.7 21.7 0 01-2.16 3.19M14.12 14.12a3 3 0 11-4.24-4.24" /><line x1="1" y1="1" x2="23" y2="23" /></svg>
                                        ) : (
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" /></svg>
                                        )}
                                    </button>
                                </div>
                            </div>

                            <div className="dc-remember">
                                <input
                                    type="checkbox"
                                    id="remember"
                                    checked={data.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                />
                                <label htmlFor="remember">Ingat saya</label>
                            </div>

                            <button type="submit" className="dc-submit" disabled={processing}>
                                Masuk
                            </button>
                        </form>

                        <div className="dc-demo-label"><span></span><span>Akun Demo</span><span></span></div>
                        <div className="dc-demo-hint">admin@valuasi.local &nbsp;/&nbsp; admin@123</div>

                        <div className="dc-back">
                            <Link href={route('landing')}>&larr; Kembali ke Beranda</Link>
                        </div>
                    </div>
                </div>
            </div>

            <style>{`
                .dc-login {
                    --dc-panel-bg: rgba(255, 255, 255, 0.65);
                    --dc-heading: #0f2c59;
                    --dc-muted: #4b6584;
                    --dc-divider: rgba(15, 44, 89, 0.12);
                    --dc-input-bg: rgba(255, 255, 255, 0.8);
                    --dc-input-border: rgba(15, 44, 89, 0.2);
                    --dc-input-text: #1e272e;
                    --dc-toggle-bg: #2563eb;
                    --dc-knob-shift: translateX(0);
                    --dc-accent: #2563eb;
                    --dc-accent-hover: #1d4ed8;
                    --dc-btn-bg: linear-gradient(180deg, #3b82f6 0%, #1d4ed8 100%);
                    --dc-btn-shadow: rgba(37, 99, 235, 0.35);
                    --dc-card-border: rgba(255, 255, 255, 0.6);
                }
                .dc-login.dark {
                    --dc-panel-bg: rgba(15, 23, 42, 0.75);
                    --dc-heading: #f8fafc;
                    --dc-muted: #94a3b8;
                    --dc-divider: rgba(255, 255, 255, 0.12);
                    --dc-input-bg: rgba(30, 41, 59, 0.8);
                    --dc-input-border: rgba(255, 255, 255, 0.15);
                    --dc-input-text: #f1f5f9;
                    --dc-toggle-bg: #3b82f6;
                    --dc-knob-shift: translateX(20px);
                    --dc-accent: #3b82f6;
                    --dc-accent-hover: #60a5fa;
                    --dc-btn-bg: linear-gradient(180deg, #60a5fa 0%, #2563eb 100%);
                    --dc-btn-shadow: rgba(0, 0, 0, 0.4);
                    --dc-card-border: rgba(255, 255, 255, 0.15);
                }
                .dc-login {
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 32px 24px;
                    box-sizing: border-box;
                    background-image: url("/images/background.png");
                    background-size: cover;
                    background-position: center;
                    background-repeat: no-repeat;
                    position: relative;
                }
                .dc-card {
                    width: 100%;
                    max-width: 1020px;
                    display: grid;
                    grid-template-columns: 1.15fr 1fr;
                    align-items: stretch;
                    border-radius: 28px;
                    overflow: hidden;
                    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.3);
                    border: 1px solid var(--dc-card-border);
                    backdrop-filter: blur(16px);
                    -webkit-backdrop-filter: blur(16px);
                    background: var(--dc-panel-bg);
                    transition: background 0.3s ease, border-color 0.3s ease;
                }
                .dc-art { position: relative; min-height: 100%; padding: 16px; display: flex; }
                .dc-art img { width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; border-radius: 18px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); }
                .dc-panel { padding: 34px 40px; display: flex; flex-direction: column; justify-content: center; background: transparent; }
                .dc-brand { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
                .dc-brand-name { display: flex; align-items: center; gap: 6px; }
                .dc-brand-name span:first-child { font-weight: 800; font-size: 16px; color: var(--dc-heading); letter-spacing: -0.2px; }
                .dc-brand-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--dc-accent); display: inline-block; margin-top: 2px; }
                .dc-toggle { width: 46px; height: 26px; border-radius: 13px; border: none; cursor: pointer; padding: 3px; box-sizing: border-box; background: var(--dc-toggle-bg); display: flex; transition: background 0.25s ease; }
                .dc-toggle-knob { width: 20px; height: 20px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.25); transform: var(--dc-knob-shift); transition: transform 0.25s ease; }
                .dc-rule { height: 1px; background: var(--dc-divider); }
                .dc-title { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: var(--dc-heading); line-height: 1.2; }
                .dc-subtitle { margin: 10px 0 0; font-size: 13.5px; color: var(--dc-muted); }
                .dc-subtitle a { color: var(--dc-accent); font-weight: 700; }
                .dc-fields { display: flex; flex-direction: column; gap: 16px; }
                .dc-input {
                    height: 50px; border-radius: 12px; border: 1px solid var(--dc-input-border); background: var(--dc-input-bg);
                    padding: 0 18px; font-size: 13.5px; font-weight: 500; color: var(--dc-input-text);
                    outline: none; box-sizing: border-box; width: 100%; font-family: inherit;
                    transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
                }
                .dc-input:focus { border-color: var(--dc-accent); box-shadow: 0 0 0 3px var(--dc-btn-shadow); background: rgba(255,255,255,0.95); }
                .dc-input::placeholder { color: var(--dc-muted); }
                .dc-login.dark .dc-input:focus { background: rgba(30, 41, 59, 0.95); }
                .dc-pw-wrap { position: relative; }
                .dc-pw-wrap .dc-input { padding-right: 46px; }
                .dc-pw-toggle {
                    position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
                    width: 36px; height: 36px; border: none; background: transparent; cursor: pointer;
                    display: flex; align-items: center; justify-content: center; color: var(--dc-muted);
                }
                .dc-remember { display: flex; align-items: center; gap: 8px; margin-top: 16px; font-size: 13px; color: var(--dc-muted); }
                .dc-remember input { accent-color: var(--dc-accent); cursor: pointer; }
                .dc-remember label { cursor: pointer; }
                .dc-submit {
                    margin-top: 24px; height: 50px; width: 100%; border: none; border-radius: 12px; cursor: pointer;
                    font-family: inherit; font-size: 14px; font-weight: 700; color: #fff;
                    background: var(--dc-btn-bg); box-shadow: 0 10px 22px var(--dc-btn-shadow); transition: filter 0.15s ease, transform 0.1s ease;
                }
                .dc-submit:hover { filter: brightness(1.08); }
                .dc-submit:active { transform: scale(0.99); }
                .dc-submit:disabled { opacity: 0.7; cursor: not-allowed; }
                .dc-demo-label { display: flex; align-items: center; gap: 12px; margin: 22px 0 12px; color: var(--dc-muted); font-size: 11px; font-weight: 700; letter-spacing: 1px; justify-content: center; }
                .dc-demo-label span:first-child, .dc-demo-label span:last-child { width: 18px; height: 1px; background: var(--dc-muted); opacity: 0.5; }
                .dc-demo-hint { text-align: center; font-size: 12px; color: var(--dc-muted); font-weight: 600; }
                .dc-back { text-align: center; margin-top: 18px; }
                .dc-back a { font-size: 13px; color: var(--dc-accent); font-weight: 700; text-decoration: none; }
                .dc-back a:hover { color: var(--dc-accent-hover); text-decoration: underline; }
                .dc-alert { margin-top: 20px; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
                @media (max-width: 1000px) {
                    .dc-card { max-width: 880px; grid-template-columns: 1fr 1fr; }
                    .dc-panel { padding: 30px 32px; }
                }
                @media (max-width: 720px) {
                    .dc-login { padding: 20px 14px; }
                    .dc-card { grid-template-columns: 1fr; max-width: 460px; border-radius: 22px; }
                    .dc-art { min-height: 0; height: 180px; padding: 12px 12px 0 12px; }
                    .dc-art img { border-radius: 14px; object-position: center 62%; }
                    .dc-panel { padding: 26px 24px 30px; }
                    .dc-title { font-size: 23px; }
                }
                @media (max-width: 380px) {
                    .dc-panel { padding: 22px 18px 26px; }
                    .dc-art { height: 140px; }
                }
            `}</style>
        </>
    );
}
