import { formatRupiah } from '../../lib/format';

const ICONS = [
    <><path d="M3 3v18h18" /><path d="M19 9l-5 5-4-4-3 3" /></>,
    <><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z" /><path d="M3.27 6.96L12 12.01l8.73-5.05" /><path d="M12 22.08V12" /></>,
    <><circle cx="12" cy="12" r="10" /><path d="M9.5 9.5h5l-5 5h5" /></>,
    <><path d="M12 1v22" /><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" /></>,
];

export default function OutputPreview({ rows, note, title = 'Pratinjau Output' }) {
    return (
        <div className="card">
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 14 }}>
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" strokeWidth="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" />
                </svg>
                <h3 style={{ fontSize: 14, fontWeight: 700 }}>{title}</h3>
            </div>

            {rows.map((row, i) => {
                let text;
                if (row.format === 'currency') text = `Rp${formatRupiah(row.value, 0)}`;
                else if (row.format === 'decimal') text = formatRupiah(row.value, row.decimals ?? 4);
                else if (row.format === 'raw') text = row.value;
                else text = formatRupiah(row.value, row.decimals ?? 2);

                return (
                    <div key={row.label} style={{ display: 'flex', alignItems: 'center', gap: 10, border: '1px solid var(--border)', borderRadius: 8, padding: 10, marginBottom: 8 }}>
                        <span style={{ width: 32, height: 32, borderRadius: 8, background: `${row.color}1A`, color: row.color, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">{ICONS[i % ICONS.length]}</svg>
                        </span>
                        <div style={{ minWidth: 0, flex: 1 }}>
                            <div style={{ fontSize: 12.5, fontWeight: 700 }}>{row.label}</div>
                            {row.sublabel && <div style={{ fontSize: 10.5, color: 'var(--text-muted)' }}>{row.sublabel}</div>}
                        </div>
                        <div style={{ textAlign: 'right', flexShrink: 0 }}>
                            <div style={{ fontSize: 14, fontWeight: 800, color: row.color }}>{text}</div>
                            {row.unit && <div style={{ fontSize: 10, color: 'var(--text-muted)' }}>{row.unit}</div>}
                        </div>
                    </div>
                );
            })}

            {note && <p style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 10, lineHeight: 1.55, fontStyle: 'italic' }}>{note}</p>}
        </div>
    );
}
