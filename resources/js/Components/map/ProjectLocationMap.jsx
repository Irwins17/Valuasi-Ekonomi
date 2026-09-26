import { useEffect, useMemo, useRef } from 'react';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { buildLegend, LABEL_KEY, paletteLookup } from '../../lib/shpStyle';

import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

const BOUNDARY_PINK = '#E8467C';
const OUTSIDE_DIM = '#5B6370';

/** simplestyle-spec keys plus our label — carried for drawing, not for reading. */
const STYLE_KEYS = new Set(['fill', 'fill-opacity', 'stroke', 'stroke-opacity', 'stroke-width', LABEL_KEY]);

function outlineStyle() {
    return {
        color: BOUNDARY_PINK,
        weight: 2.5,
        opacity: 1,
        dashArray: '5, 5',
        fill: false,
        interactive: false,
    };
}

/**
 * Paints a feature with the colours the shapefile itself supplied (see
 * lib/shpStyle.js): its class's entry in the layer palette, or — for a polygon
 * with a colour but no class — style properties on the feature itself.
 * Anything with neither keeps the plain survey-area outline.
 */
function makeFeatureStyle(palette) {
    return (feature) => {
        const props = feature?.properties ?? {};
        const style = palette.get(props[LABEL_KEY]) ?? props;

        if (!style.fill && !style.stroke) return outlineStyle();

        return {
            color: style.stroke ?? style.fill,
            weight: style['stroke-width'] ?? 1.2,
            opacity: style['stroke-opacity'] ?? 1,
            fill: Boolean(style.fill),
            fillColor: style.fill,
            fillOpacity: style['fill-opacity'] ?? 0.7,
            // Leaflet otherwise performs another fairly aggressive screen-
            // space simplification. Keep it very low because the uploader has
            // already applied a controlled data-size budget.
            smoothFactor: 0.1,
            lineCap: 'round',
            lineJoin: 'round',
            interactive: true,
        };
    };
}

const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
}[c]));

/**
 * The attribute table for one polygon, minus the styling keys we added.
 * This is the "keterangan" a reader is after when they click a polygon.
 */
function attributePopup(props) {
    const attributes = Object.entries(props)
        .filter(([key, value]) => !STYLE_KEYS.has(key) && value !== null && value !== undefined && String(value).trim() !== '');
    const rows = attributes
        .map(([key, value]) => `
            <tr>
                <th style="text-align:left;vertical-align:top;padding:4px 12px 4px 0;font-weight:600;color:#52514e;white-space:nowrap;border-bottom:1px solid #eee">${escapeHtml(key)}</th>
                <td style="vertical-align:top;padding:4px 0;border-bottom:1px solid #eee;overflow-wrap:anywhere">${escapeHtml(value)}</td>
            </tr>`)
        .join('');

    const title = props[LABEL_KEY]
        ? `<div style="font-size:14px;font-weight:700;margin-bottom:2px">${escapeHtml(props[LABEL_KEY])}</div>`
        : '';
    const summary = attributes.length
        ? `<div style="font-size:11px;color:#777;margin-bottom:7px">${attributes.length} atribut pada poligon ini</div>`
        : '';

    if (!rows) return title || null;

    return `${title}${summary}<table style="width:100%;border-collapse:collapse;font-size:12px">${rows}</table>`;
}

/**
 * Legend for the layer, laid out under the map rather than floating over it:
 * a land-cover file runs to a dozen-plus classes, and a panel that size covers
 * the very polygons it is describing.
 */
function MapLegend({ entries }) {
    const total = entries.reduce((sum, entry) => sum + entry.count, 0);

    return (
        <div style={{ padding: '12px 14px 14px', background: 'var(--surface)' }}>
            <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between', gap: 12, marginBottom: 3 }}>
                <div style={{ fontSize: 13, fontWeight: 700 }}>Keterangan SHP</div>
                <div style={{ fontSize: 11, color: 'var(--text-muted)', textAlign: 'right' }}>
                    {entries.length.toLocaleString('id-ID')} kelas · {total.toLocaleString('id-ID')} poligon
                </div>
            </div>
            <div style={{ fontSize: 11, color: 'var(--text-muted)', marginBottom: 10 }}>
                Klik poligon pada peta untuk melihat seluruh atributnya.
            </div>
            <div style={{
                display: 'grid',
                gridTemplateColumns: 'repeat(auto-fill, minmax(260px, 1fr))',
                gap: '7px 18px',
            }}>
                {entries.map((entry) => (
                    <div
                        key={entry.label}
                        title={`${entry.label} — ${entry.count.toLocaleString('id-ID')} poligon (${total ? (entry.count / total * 100).toLocaleString('id-ID', { maximumFractionDigits: 1 }) : 0}%)`}
                        style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 12, minWidth: 0 }}
                    >
                        <span style={{
                            flex: '0 0 14px',
                            height: 14,
                            borderRadius: 2,
                            background: entry.color,
                            border: '1px solid rgba(0,0,0,0.25)',
                        }} />
                        <span style={{ flex: '0 1 auto', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                            {entry.label}
                        </span>
                        <span style={{ flex: '0 0 auto', marginLeft: 'auto', color: 'var(--text-muted)', fontSize: 11, whiteSpace: 'nowrap' }}>
                            {entry.count.toLocaleString('id-ID')} · {total
                                ? (entry.count / total * 100).toLocaleString('id-ID', { maximumFractionDigits: 1 })
                                : 0}%
                        </span>
                    </div>
                ))}
            </div>
        </div>
    );
}

const WORLD_RING = [[-90, -180], [-90, 180], [90, 180], [90, -180]];
const maskStyle = {
    stroke: false,
    fill: true,
    fillColor: OUTSIDE_DIM,
    fillOpacity: 0.45,
    interactive: false,
};

function collectRings(geojson) {
    const rings = [];
    const addPolygon = (polygon) => {
        if (polygon?.length) {
            rings.push(polygon[0].map(([lng, lat]) => [lat, lng]));
        }
    };

    const visit = (geometry) => {
        if (!geometry) return;
        if (geometry.type === 'Polygon') {
            addPolygon(geometry.coordinates);
        } else if (geometry.type === 'MultiPolygon') {
            geometry.coordinates.forEach(addPolygon);
        } else if (geometry.type === 'GeometryCollection') {
            geometry.geometries?.forEach(visit);
        }
    };

    if (geojson.type === 'FeatureCollection') {
        geojson.features.forEach((f) => visit(f.geometry));
    } else if (geojson.type === 'Feature') {
        visit(geojson.geometry);
    } else {
        visit(geojson);
    }

    return rings;
}

export default function ProjectLocationMap({
    boundary = null,
    center = null,
    zoom = 12,
    label = 'Lokasi Survey',
    height = 260,
    style,
    className,
}) {
    const containerRef = useRef(null);
    const mapRef = useRef(null);
    const overlayRef = useRef(null);

    // Derived, not drawn on the map: the legend lives in the DOM below it.
    const legend = useMemo(() => (boundary ? buildLegend(boundary) : []), [boundary]);
    useEffect(() => {
        if (!containerRef.current || mapRef.current) return;
        const map = L.map(containerRef.current, {
            scrollWheelZoom: false,
            zoomSnap: 0,
            zoomDelta: 0.5,
            // Thousands of SHP features are much lighter on one canvas than
            // as thousands of individual SVG paths.
            preferCanvas: true,
        });
        mapRef.current = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 18,
        }).addTo(map);

        return () => {
            map.remove();
            mapRef.current = null;
            overlayRef.current = null;
        };
    }, []);

    useEffect(() => {
        const map = mapRef.current;
        if (!map) return;

        if (overlayRef.current) {
            map.removeLayer(overlayRef.current);
            overlayRef.current = null;
        }

        let group = null;
        if (boundary) {
            try {
                const palette = paletteLookup(boundary);
                const styled = palette.size > 0
                    || (boundary.features ?? []).some((f) => f?.properties?.fill);

                const layer = L.geoJSON(boundary, {
                    style: makeFeatureStyle(palette),
                    onEachFeature: styled
                        ? (feature, featureLayer) => {
                            const props = feature?.properties ?? {};
                            const name = props[LABEL_KEY];
                            if (name) featureLayer.bindTooltip(String(name), { sticky: true });

                            const popup = attributePopup(props);
                            if (popup) featureLayer.bindPopup(popup, { maxWidth: 420, maxHeight: 320 });
                        }
                        : undefined,
                });

                const rings = styled ? [] : collectRings(boundary);
                const layers = rings.length
                    ? [L.polygon([WORLD_RING, ...rings], maskStyle), layer]
                    : [layer];

                group = L.layerGroup(layers).addTo(map);
            } catch (err) {
                console.error('ProjectLocationMap: failed to render boundary', err);
                if (group) map.removeLayer(group);
                group = null;
            }
        }

        let fitOk = false;
        if (group) {
            try {
                const bounds = L.geoJSON(boundary).getBounds();
                if (bounds.isValid()) {
                    map.fitBounds(bounds, { padding: [20, 20] });
                    fitOk = true;
                }
            } catch (err) {
                console.error('ProjectLocationMap: failed to fit boundary bounds', err);
            }
        }

        if (fitOk) {
            overlayRef.current = group;

            return;
        }

        if (group) map.removeLayer(group);

        if (center) {
            map.setView(center, zoom);
            overlayRef.current = L.marker(center).addTo(map).bindPopup(label);
        } else {
            map.setView([-2.5, 118], 5);
        }
    }, [boundary, center, zoom, label]);

    return (
        <div className={className} style={{ width: '100%', ...style }}>
            <div ref={containerRef} style={{ height, width: '100%' }} />
            {legend.length > 0 && <MapLegend entries={legend} />}
            <style>{`
                .leaflet-interactive {
                    transition: fill-opacity 180ms ease, stroke-width 180ms ease;
                }
            `}</style>
        </div>
    );
}
