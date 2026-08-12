import { formatRupiah } from '../../../lib/format';

/**
 * "Tarik dari Modul Hasil Valuasi" — picks a computed module result and hands
 * it to the benefit form.
 *
 * The point is not saving keystrokes. A benefit typed by hand is a number
 * nobody can re-check; one taken from here keeps `source_module` +
 * `source_record_id`, so the figure can be traced back to the EOP row or CVM
 * analysis it came from and re-read when the module data changes.
 *
 * `sources` is ModuleBenefitSources::forProject() as-is: keyed by module, each
 * entry already carrying the description, amount, year, unit and service group
 * the form needs.
 */
export default function ModuleSourcePicker({ sources, labels, selectedModule, selectedRecordId, onPickModule, onPickRecord }) {
    const options = sources[selectedModule] || [];
    const isManual = !selectedModule || selectedModule === 'manual';
    const selected = options.find((o) => String(o.id) === String(selectedRecordId));

    // Modules with nothing recorded yet are still listed, but labelled, so an
    // empty dropdown never looks like a broken page.
    const moduleEntries = Object.entries(labels).map(([key, label]) => {
        if (key === 'manual') return [key, label];
        const count = (sources[key] || []).length;
        return [key, count ? `${label} (${count})` : `${label} — belum ada data`];
    });

    return (
        <div style={{ border: '1px solid var(--border)', borderLeft: '4px solid var(--primary)', borderRadius: 8, padding: 14, marginBottom: 20, background: 'var(--surface-alt)' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 4 }}>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" strokeWidth="2">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" /><path d="M7 10l5 5 5-5" /><path d="M12 15V3" />
                </svg>
                <h4 style={{ fontSize: 13.5, fontWeight: 700 }}>Tarik dari Modul Hasil Valuasi</h4>
            </div>
            <p style={{ fontSize: 11.5, color: 'var(--text-muted)', marginBottom: 12, lineHeight: 1.5 }}>
                Pilih hasil perhitungan modul untuk mengisi manfaat ini otomatis. Nilai tetap dapat disunting,
                dan tautan ke record asal disimpan agar angkanya bisa ditelusuri kembali.
            </p>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                <div className="form-group" style={{ marginBottom: 0 }}>
                    <label className="form-label">Sumber Modul</label>
                    <select className="form-input" value={selectedModule || 'manual'} onChange={(e) => onPickModule(e.target.value)}>
                        {moduleEntries.map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                    </select>
                </div>
                <div className="form-group" style={{ marginBottom: 0 }}>
                    <label className="form-label">Record Sumber</label>
                    <select
                        className="form-input"
                        value={selectedRecordId ?? ''}
                        onChange={(e) => onPickRecord(e.target.value)}
                        disabled={isManual || options.length === 0}
                        style={isManual || options.length === 0 ? { background: 'var(--surface-alt)', cursor: 'not-allowed' } : undefined}
                    >
                        <option value="">{isManual ? '— Input manual —' : options.length ? '— Pilih record —' : '— Belum ada data modul —'}</option>
                        {options.map((o) => <option key={o.id} value={o.id}>{o.label}</option>)}
                    </select>
                </div>
            </div>

            {selected && (
                <div style={{ marginTop: 12, paddingTop: 10, borderTop: '1px dashed var(--border)', fontSize: 12, display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(130px, 1fr))', gap: 10 }}>
                    <div>
                        <div style={{ fontSize: 10.5, color: 'var(--text-muted)' }}>Nilai modul</div>
                        <strong>Rp{formatRupiah(selected.value, 0)}</strong>
                    </div>
                    <div>
                        <div style={{ fontSize: 10.5, color: 'var(--text-muted)' }}>Tahun</div>
                        <strong>{selected.period_year ?? '—'}</strong>
                    </div>
                    <div>
                        <div style={{ fontSize: 10.5, color: 'var(--text-muted)' }}>Satuan</div>
                        <strong>{selected.unit || '—'}</strong>
                    </div>
                    <div style={{ gridColumn: '1 / -1', color: 'var(--text-muted)', fontSize: 11.5 }}>
                        {selected.detail}{selected.basis ? ` · basis ${selected.basis}` : ''}
                    </div>
                </div>
            )}
        </div>
    );
}
