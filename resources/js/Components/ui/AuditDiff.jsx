/**
 * Defensively handles both correctly-encoded rows (array/object) and any
 * pre-existing rows written before the AuditLog::log() double-json_encode
 * bug was fixed (where the value comes back as a JSON string, not an object).
 */
function normalize(value) {
    if (value == null) return null;
    if (typeof value === 'string') {
        try {
            return JSON.parse(value);
        } catch {
            return value;
        }
    }
    return value;
}

function truncate(str, len) {
    return str.length > len ? str.slice(0, len) + '…' : str;
}

export default function AuditDiff({ oldValues, newValues }) {
    const before = normalize(oldValues);
    const after = normalize(newValues);

    if (!before && !after) {
        return <span style={{ fontSize: 12, color: 'var(--text-muted)' }}>-</span>;
    }

    return (
        <details>
            <summary style={{ fontSize: 12, color: 'var(--primary)', cursor: 'pointer' }}>Lihat detail</summary>
            <div style={{ marginTop: 8, fontSize: 11, maxWidth: 400, overflowX: 'auto' }}>
                {before && (
                    <div style={{ marginBottom: 4 }}>
                        <strong>Before:</strong> <code style={{ fontSize: 11 }}>{truncate(JSON.stringify(before), 200)}</code>
                    </div>
                )}
                {after && (
                    <div>
                        <strong>After:</strong> <code style={{ fontSize: 11 }}>{truncate(JSON.stringify(after), 200)}</code>
                    </div>
                )}
            </div>
        </details>
    );
}
