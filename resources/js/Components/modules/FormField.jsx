const ADORNMENT = {
    display: 'flex',
    alignItems: 'center',
    padding: '0 12px',
    background: 'var(--surface-alt)',
    border: '1.5px solid var(--border)',
    fontSize: 12,
    color: 'var(--text-secondary)',
    whiteSpace: 'nowrap',
};

export default function FormField({ field, value, onChange, error }) {
    const readOnlyStyle = field.readOnly
        ? { background: 'var(--surface-alt)', color: 'var(--text-secondary)', cursor: 'not-allowed' }
        : undefined;

    function wrap(control) {
        return (
            <div className="form-group" style={field.full ? { gridColumn: '1 / -1' } : undefined}>
                <label className="form-label">
                    {field.label}
                    {field.required && <span style={{ color: 'var(--danger)', marginLeft: 3 }}>*</span>}
                </label>
                {control}
                {field.hint && <p className="form-hint">{field.hint}</p>}
                {error && <p className="form-error">{error}</p>}
            </div>
        );
    }

    if (field.type === 'checkbox') {
        return (
            <div className="form-group" style={field.full ? { gridColumn: '1 / -1' } : undefined}>
                <label style={{ display: 'flex', alignItems: 'flex-start', gap: 10, cursor: 'pointer' }}>
                    <input
                        type="checkbox"
                        checked={Boolean(value)}
                        onChange={(e) => onChange(e.target.checked)}
                        style={{ width: 16, height: 16, marginTop: 2, accentColor: 'var(--primary)', cursor: 'pointer' }}
                    />
                    <span>
                        <span style={{ fontWeight: 600, fontSize: 14 }}>{field.label}</span>
                        {field.hint && <span style={{ display: 'block', fontSize: 12, color: 'var(--text-muted)', marginTop: 2 }}>{field.hint}</span>}
                    </span>
                </label>
                {error && <p className="form-error">{error}</p>}
            </div>
        );
    }

    if (field.type === 'select') {
        return wrap(
            <select className="form-input" value={value ?? ''} onChange={(e) => onChange(e.target.value)} required={field.required} disabled={field.readOnly} style={readOnlyStyle}>
                <option value="">{field.placeholder || '— Pilih —'}</option>
                {(field.options || []).map((opt) => {
                    const [val, label] = Array.isArray(opt) ? opt : [opt, opt];
                    return <option key={val} value={val}>{label}</option>;
                })}
            </select>
        );
    }

    if (field.type === 'year') {
        const thisYear = new Date().getFullYear();
        const years = Array.from({ length: 26 }, (_, i) => thisYear + 2 - i);
        if (value && !years.includes(Number(value))) {
            years.push(Number(value));
            years.sort((a, b) => b - a);
        }
        return wrap(
            <select className="form-input" value={value ?? ''} onChange={(e) => onChange(e.target.value)} required={field.required}>
                <option value="">{field.placeholder || 'Pilih periode / tahun'}</option>
                {years.map((y) => <option key={y} value={y}>{y}</option>)}
            </select>
        );
    }

    if (field.type === 'textarea') {
        return wrap(
            <textarea
                className="form-input"
                rows={field.rows || 3}
                maxLength={field.maxLength}
                value={value ?? ''}
                onChange={(e) => onChange(e.target.value)}
                placeholder={field.placeholder}
                required={field.required}
            />
        );
    }

    const isNumeric = field.type === 'number' || field.type === 'currency';
    const input = (
        <input
            type={isNumeric ? 'number' : field.type === 'date' ? 'date' : 'text'}
            step={isNumeric ? (field.step || 'any') : undefined}
            min={isNumeric && field.allowNegative !== true ? 0 : undefined}
            className="form-input"
            value={value ?? ''}
            onChange={(e) => onChange(e.target.value)}
            placeholder={field.placeholder}
            required={field.required}
            readOnly={field.readOnly}
            style={{
                ...readOnlyStyle,
                ...(field.prefix || field.suffix
                    ? {
                        flex: 1,
                        borderTopLeftRadius: field.prefix ? 0 : undefined,
                        borderBottomLeftRadius: field.prefix ? 0 : undefined,
                        borderTopRightRadius: field.suffix ? 0 : undefined,
                        borderBottomRightRadius: field.suffix ? 0 : undefined,
                    }
                    : {}),
            }}
        />
    );

    if (!field.prefix && !field.suffix) return wrap(input);

    return wrap(
        <div style={{ display: 'flex', alignItems: 'stretch' }}>
            {field.prefix && (
                <span style={{ ...ADORNMENT, borderRight: 'none', borderRadius: 'var(--radius-sm) 0 0 var(--radius-sm)', fontWeight: 600, fontSize: 13 }}>{field.prefix}</span>
            )}
            {input}
            {field.suffix && (
                <span style={{ ...ADORNMENT, borderLeft: 'none', borderRadius: '0 var(--radius-sm) var(--radius-sm) 0' }}>{field.suffix}</span>
            )}
        </div>
    );
}
