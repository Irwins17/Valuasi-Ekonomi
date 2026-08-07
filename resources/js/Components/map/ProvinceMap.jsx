import { useMemo, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import indonesiaGeo from '../../data/indonesia-provinces.json';
import { PROVINCES } from '../../lib/provinces';
import { toNumber, formatTriliun } from '../../lib/format';

const EMPTY_COLOR = '#e2e8f0';
const COLOR_FROM = [199, 210, 254]; // indigo-200
const COLOR_TO = [67, 56, 202]; // indigo-700 (var(--primary-dark) family)

function interpolateColor(t) {
    const r = Math.round(COLOR_FROM[0] + (COLOR_TO[0] - COLOR_FROM[0]) * t);
    const g = Math.round(COLOR_FROM[1] + (COLOR_TO[1] - COLOR_FROM[1]) * t);
    const b = Math.round(COLOR_FROM[2] + (COLOR_TO[2] - COLOR_FROM[2]) * t);
    return `rgb(${r},${g},${b})`;
}

function colorForCount(count, maxCount) {
    if (!count) return EMPTY_COLOR;
    if (maxCount <= 0) return EMPTY_COLOR;
    return interpolateColor(0.25 + 0.75 * (count / maxCount));
}

function forEachCoord(geometry, fn) {
    const polys = geometry.type === 'MultiPolygon' ? geometry.coordinates : [geometry.coordinates];
    polys.forEach((poly) => poly.forEach((ring) => ring.forEach(fn)));
}

function featureToPath(geometry, project) {
    const polys = geometry.type === 'MultiPolygon' ? geometry.coordinates : [geometry.coordinates];
    return polys
        .map((poly) =>
            poly
                .map((ring) => 'M' + ring.map((pt) => project(pt).map((n) => n.toFixed(1)).join(',')).join('L') + 'Z')
                .join(' ')
        )
        .join(' ');
}

const VIEW_W = 780;
const VIEW_H = 320;

function useIndonesiaProjection() {
    return useMemo(() => {
        let minLon = Infinity, maxLon = -Infinity, minLat = Infinity, maxLat = -Infinity;
        indonesiaGeo.features.forEach((f) => {
            forEachCoord(f.geometry, ([lon, lat]) => {
                if (lon < minLon) minLon = lon;
                if (lon > maxLon) maxLon = lon;
                if (lat < minLat) minLat = lat;
                if (lat > maxLat) maxLat = lat;
            });
        });
        const padding = 8;
        const lonRange = maxLon - minLon;
        const latRange = maxLat - minLat;
        const scale = Math.min((VIEW_W - padding * 2) / lonRange, (VIEW_H - padding * 2) / latRange);
        const offsetX = (VIEW_W - lonRange * scale) / 2;
        const offsetY = (VIEW_H - latRange * scale) / 2;
        return ([lon, lat]) => [offsetX + (lon - minLon) * scale, offsetY + (maxLat - lat) * scale];
    }, []);
}

export default function ProvinceMap({ projects = [], height = 460 }) {
    const [selected, setSelected] = useState(null);
    const [hovered, setHovered] = useState(null);
    const project = useIndonesiaProjection();

    const stats = useMemo(() => {
        const map = new Map();
        (projects || []).forEach((p) => {
            if (!p.province) return;
            const row = map.get(p.province) || { count: 0, tev: 0, projects: [] };
            row.count += 1;
            row.tev += toNumber(p.tev);
            row.projects.push(p);
            map.set(p.province, row);
        });
        return map;
    }, [projects]);

    const maxCount = useMemo(() => {
        let max = 0;
        stats.forEach((row) => { if (row.count > max) max = row.count; });
        return max;
    }, [stats]);

    const selectedStats = selected ? stats.get(selected) : null;

    return (
        <div className="card" style={{ padding: 0, overflow: 'hidden', position: 'relative', height, background: 'linear-gradient(180deg,#eef2ff 0%,#f8fafc 100%)' }}>
            <svg viewBox={`0 0 ${VIEW_W} ${VIEW_H}`} style={{ width: '100%', height: '100%', display: 'block' }} preserveAspectRatio="xMidYMid meet">
                {indonesiaGeo.features.map((f) => {
                    const name = f.properties.PROVINSI;
                    const row = stats.get(name);
                    const count = row?.count || 0;
                    const isSelected = selected === name;
                    const isHovered = hovered === name;
                    return (
                        <path
                            key={f.properties.id}
                            d={featureToPath(f.geometry, project)}
                            fill={colorForCount(count, maxCount)}
                            stroke={isSelected ? '#4338ca' : isHovered ? '#818cf8' : '#ffffff'}
                            strokeWidth={isSelected ? 1.6 : isHovered ? 1.2 : 0.6}
                            style={{ cursor: 'pointer', transition: 'fill 0.2s, stroke 0.15s' }}
                            onMouseEnter={() => setHovered(name)}
                            onMouseLeave={() => setHovered((h) => (h === name ? null : h))}
                            onClick={() => {
                                // Single project in this province: skip the selection panel
                                // and go straight to its detail page. Multiple projects: fall
                                // back to the drill-down panel so the user can pick one.
                                if (row?.count === 1) {
                                    router.visit(route('public.project', row.projects[0].id));
                                    return;
                                }
                                setSelected((s) => (s === name ? null : name));
                            }}
                        >
                            <title>{count > 0 ? `${name} — ${count} proyek` : name}</title>
                        </path>
                    );
                })}
            </svg>

            {/* Province select, floating directly on the map */}
            <div className="province-map-select-box">
                <label style={{ fontSize: 10.5, fontWeight: 600, color: 'var(--text-muted)', textTransform: 'uppercase', display: 'block', marginBottom: 5 }}>Pilih Provinsi</label>
                <select
                    className="province-map-select"
                    value={selected || ''}
                    onChange={(e) => setSelected(e.target.value || null)}
                >
                    <option value="">— Semua Provinsi —</option>
                    {PROVINCES.map((p) => (
                        <option key={p} value={p}>{p}{stats.get(p)?.count ? ` (${stats.get(p).count})` : ''}</option>
                    ))}
                </select>
            </div>

            {!selected && (
                <div style={{ position: 'absolute', bottom: 14, left: 16, background: 'rgba(255,255,255,0.9)', backdropFilter: 'blur(6px)', borderRadius: 999, padding: '6px 14px', fontSize: 12.5, color: 'var(--text-secondary)', display: 'flex', alignItems: 'center', gap: 6, boxShadow: 'var(--shadow-sm)' }}>
                    📍 Klik provinsi untuk melihat detail
                </div>
            )}

            {!selected && maxCount > 0 && (
                <div style={{ position: 'absolute', bottom: 14, right: 16, background: 'rgba(255,255,255,0.9)', backdropFilter: 'blur(6px)', borderRadius: 10, padding: '8px 12px', boxShadow: 'var(--shadow-sm)' }}>
                    <div style={{ fontSize: 11, color: 'var(--text-muted)', marginBottom: 4 }}>Jumlah Proyek</div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                        <span style={{ fontSize: 11, color: 'var(--text-secondary)' }}>0</span>
                        <div style={{ width: 60, height: 8, borderRadius: 4, background: `linear-gradient(90deg, ${EMPTY_COLOR}, ${colorForCount(maxCount, maxCount)})` }} />
                        <span style={{ fontSize: 11, color: 'var(--text-secondary)' }}>{maxCount}</span>
                    </div>
                </div>
            )}

            {/* Detail panel, floating over the map instead of a separate column */}
            {selected && (
                <div className="province-map-panel">
                    <button type="button" onClick={() => setSelected(null)} aria-label="Tutup" style={{ position: 'absolute', top: 12, right: 12, border: 'none', background: 'var(--surface-alt)', width: 26, height: 26, borderRadius: '50%', cursor: 'pointer', fontSize: 13, lineHeight: 1, color: 'var(--text-secondary)' }}>✕</button>

                    <div style={{ paddingRight: 24 }}>
                        <h4 style={{ fontSize: 16, fontWeight: 700 }}>{selected}</h4>
                        <p style={{ fontSize: 12.5, color: 'var(--text-secondary)', marginTop: 2 }}>
                            {selectedStats ? `${selectedStats.count} proyek dipublikasikan` : 'Belum ada proyek dipublikasikan'}
                        </p>
                    </div>

                    {selectedStats && (
                        <>
                            <div style={{ background: 'var(--surface-alt)', borderRadius: 'var(--radius-sm)', padding: 12, marginTop: 14 }}>
                                <div style={{ fontSize: 11, color: 'var(--text-muted)', fontWeight: 600, textTransform: 'uppercase' }}>Total TEV</div>
                                <div style={{ fontSize: 20, fontWeight: 800, color: 'var(--primary)' }}>Rp{formatTriliun(selectedStats.tev)}T</div>
                            </div>

                            <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginTop: 14 }}>
                                {selectedStats.projects.map((p) => (
                                    <Link
                                        key={p.id}
                                        href={route('public.project', p.id)}
                                        style={{ display: 'block', padding: '10px 12px', border: '1px solid var(--border-light)', borderRadius: 'var(--radius-sm)', textDecoration: 'none', color: 'var(--text)' }}
                                    >
                                        <div style={{ fontSize: 13, fontWeight: 600 }}>{p.name}</div>
                                        <div style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 2 }}>{p.location}</div>
                                    </Link>
                                ))}
                            </div>
                        </>
                    )}
                </div>
            )}
        </div>
    );
}
