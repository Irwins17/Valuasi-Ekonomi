export default function FormulaPanel({ title, formulas, legend = [], note, icon }) {
    const list = Array.isArray(formulas) ? formulas : [formulas].filter(Boolean);

    return (
        <div className="card" style={{ marginBottom: 16 }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 14 }}>
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" strokeWidth="2">
                    {icon || <><circle cx="12" cy="12" r="10" /><path d="M12 16v-4" /><path d="M12 8h.01" /></>}
                </svg>
                <h3 style={{ fontSize: 14, fontWeight: 700 }}>{title}</h3>
            </div>

            {list.map((f, i) => (
                <div key={i} style={{ background: 'var(--primary-50)', border: '1px solid var(--border)', borderRadius: 8, padding: '12px 12px', textAlign: 'center', marginBottom: 8 }}>
                    <span style={{ fontFamily: 'ui-monospace, SFMono-Regular, Menlo, monospace', fontSize: 13.5, fontWeight: 700, color: 'var(--primary-dark)', whiteSpace: 'pre-line', lineHeight: 1.6 }}>{f}</span>
                </div>
            ))}

            {legend.length > 0 && (
                <div style={{ background: 'var(--surface-alt)', borderRadius: 8, padding: 12, marginTop: 10 }}>
                    {legend.map((row) => (
                        <div key={row.sym} style={{ display: 'flex', gap: 8, fontSize: 11.5, marginBottom: 6, lineHeight: 1.5 }}>
                            <strong style={{ fontFamily: 'ui-monospace, SFMono-Regular, Menlo, monospace', color: 'var(--primary-dark)', minWidth: 44, flexShrink: 0 }}>{row.sym}</strong>
                            <span style={{ color: 'var(--text-muted)' }}>= {row.desc}</span>
                        </div>
                    ))}
                </div>
            )}

            {note && <p style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 10, lineHeight: 1.55 }}>{note}</p>}
        </div>
    );
}
