import { useRef, useState } from 'react';
import shp from 'shpjs';
import ProjectLocationMap from './ProjectLocationMap';
import {
    boundaryCentroid,
    countFeatures,
    countCoordinates,
    findInvalidCoordinate,
    roundCoordinatePrecision,
    simplifyToBudget,
} from '../../lib/geo';
import { applyLayerStyle, readStyleFiles } from '../../lib/shpStyle';

// Above this, a boundary stops being a lightweight preview polygon and
// starts being a multi-megabyte blob that has to round-trip through every
// page that renders the project. Files over the budget are no longer
// rejected: they get thinned with Douglas-Peucker right here in the browser,
// so an unsimplified high-detail shapefile can be uploaded as-is.
// A thematic SHP can contain thousands of small adjacent polygons. Keep a
// generous detail budget so shared coastlines/borders still follow the source
// when inspected at Leaflet's maximum zoom. The server accepts large request
// payloads and list pages do not ship boundary_geojson, so this detail is paid
// only on pages that actually need the map.
const MAX_COORDINATES = 750000;

// Ceiling on what we'll even try to parse and thin in the browser; past this
// the tab would stall long enough to look frozen.
const HARD_MAX_COORDINATES = 3000000;

const MAX_FILE_BYTES = 150 * 1024 * 1024;

const formatCount = (n) => n.toLocaleString('id-ID');

/** One sentence on where the map's colours and class names came from. */
function describeStyle({ source, labelField, colorField, classCount }, styleFiles) {
    const unsupported = styleFiles.unsupported?.length
        ? ` File ${styleFiles.unsupported.join(', ')} (ArcGIS) tidak bisa dibaca — ekspor simbologi ke .qml atau .sld agar warnanya ikut terpakai.`
        : '';

    const classes = classCount ? ` ${classCount} kelas` : '';

    switch (source) {
        case 'qml':
            return `Warna dan keterangan mengikuti simbologi QGIS (.qml) dari kolom "${labelField}".${classes ? classes + ' ditampilkan di legenda.' : ''}${unsupported}`;
        case 'sld':
            return `Warna dan keterangan mengikuti berkas simbologi .sld dari kolom "${labelField}".${classes ? classes + ' ditampilkan di legenda.' : ''}${unsupported}`;
        case 'attribute':
            return `Warna diambil dari kolom "${colorField}" pada tabel atribut, keterangan dari kolom "${labelField}".${unsupported}`;
        case 'category':
            return `File tidak menyertakan simbologi, jadi warna dibuat otomatis per kelas pada kolom "${labelField}" (${classCount} kelas).${unsupported}`;
        default:
            return unsupported.trim() || null;
    }
}

function normalizeToFeatureCollection(parsed) {
    const layers = Array.isArray(parsed) ? parsed : [parsed];
    return {
        type: 'FeatureCollection',
        features: layers.flatMap((layer) => layer?.features ?? []),
    };
}

/**
 * Drop/click zone that reads a zipped ArcGIS shapefile (.shp/.dbf/.shx/.prj
 * bundled as .zip) entirely client-side via shpjs, converts it to GeoJSON
 * (reprojected to WGS84 when a .prj is present) and reports the polygon
 * plus its bounding-box center back to the parent form.
 */
export default function ShpUploader({ value = null, onParsed, onClear, height = 360 }) {
    const inputRef = useRef(null);
    const [dragOver, setDragOver] = useState(false);
    const [loading, setLoading] = useState(false);
    const [fileName, setFileName] = useState(null);
    const [error, setError] = useState(null);
    const [notice, setNotice] = useState(null);

    async function handleFile(file) {
        if (!file) return;
        if (!file.name.toLowerCase().endsWith('.zip')) {
            setError('File harus berupa arsip .zip berisi .shp, .dbf, .shx, dan .prj');
            return;
        }
        if (file.size > MAX_FILE_BYTES) {
            setError(`Ukuran file ${(file.size / 1048576).toFixed(1)} MB melebihi batas ${MAX_FILE_BYTES / 1048576} MB.`);
            return;
        }

        setLoading(true);
        setError(null);
        setNotice(null);
        try {
            const buffer = await file.arrayBuffer();
            const parsed = await shp(buffer);
            const geojson = normalizeToFeatureCollection(parsed);

            if (!geojson.features.length) {
                throw new Error('Tidak ada fitur geometri yang ditemukan di dalam file');
            }

            const invalid = findInvalidCoordinate(geojson);
            if (invalid) {
                throw new Error('Koordinat pada file tidak valid (lat/long di luar jangkauan atau rusak). Pastikan file menyertakan .prj yang benar dan menggunakan sistem koordinat geografis (WGS 1984).');
            }

            const pointCount = countCoordinates(geojson);
            if (pointCount > HARD_MAX_COORDINATES) {
                throw new Error(`File terlalu besar untuk diproses di browser (${formatCount(pointCount)} titik koordinat, batas ${formatCount(HARD_MAX_COORDINATES)}). Sederhanakan polygon di software GIS (mis. "Simplify Geometry" di QGIS/ArcGIS) sebelum diunggah.`);
            }

            // Centroid comes from the original geometry — simplification can
            // nudge the bounding box, and the project's coordinate should
            // reflect the file as uploaded.
            const centroid = boundaryCentroid(geojson);
            if (!centroid) {
                throw new Error('Gagal membaca koordinat dari file SHP');
            }

            const { geojson: fitted, simplified, before, after } = simplifyToBudget(geojson, MAX_COORDINATES);
            const styled = roundCoordinatePrecision(fitted);

            // Colour and class names come from the upload itself — a .qml/.sld
            // beside the .shp, or the attribute table — so the map matches what
            // the file looks like in QGIS/ArcGIS instead of a house style.
            const styleFiles = await readStyleFiles(buffer);
            const applied = applyLayerStyle(styled, styleFiles);

            setFileName(file.name);
            setNotice([
                simplified
                    ? `Polygon disederhanakan otomatis dari ${formatCount(before)} menjadi ${formatCount(after)} titik koordinat agar ringan dibuka. Bentuk batas area tetap dipertahankan.`
                    : null,
                describeStyle(applied, styleFiles),
            ].filter(Boolean).join(' '));
            onParsed?.(styled, centroid);
        } catch (err) {
            setError(err.message || 'Gagal membaca file SHP');
        } finally {
            setLoading(false);
        }
    }

    function clear() {
        setFileName(null);
        setError(null);
        setNotice(null);
        if (inputRef.current) inputRef.current.value = '';
        onClear?.();
    }

    if (value) {
        return (
            <div>
                <ProjectLocationMap boundary={value} height={height} style={{ borderRadius: 'var(--radius-sm)', border: '1px solid var(--border)', overflow: 'hidden' }} />
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: 8, gap: 12 }}>
                    <span style={{ fontSize: 12, color: 'var(--text-muted)' }}>
                        {fileName ? `${fileName} — ` : ''}{countFeatures(value)} fitur polygon berhasil dibaca
                        {` (${formatCount(countCoordinates(value))} titik)`}
                    </span>
                    <button type="button" className="btn btn-sm btn-ghost" onClick={clear}>Ganti file</button>
                </div>
                {notice && <p style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 6 }}>{notice}</p>}
            </div>
        );
    }

    return (
        <div>
            <div
                onClick={() => inputRef.current?.click()}
                onDragOver={(e) => { e.preventDefault(); setDragOver(true); }}
                onDragLeave={() => setDragOver(false)}
                onDrop={(e) => {
                    e.preventDefault();
                    setDragOver(false);
                    handleFile(e.dataTransfer.files?.[0]);
                }}
                style={{
                    border: `1.5px dashed ${dragOver ? 'var(--primary)' : 'var(--border)'}`,
                    borderRadius: 'var(--radius-sm)',
                    height,
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    justifyContent: 'center',
                    textAlign: 'center',
                    cursor: 'pointer',
                    background: dragOver ? 'var(--primary-50)' : 'var(--surface-alt)',
                    transition: 'all 0.15s',
                }}
            >
                <input
                    ref={inputRef}
                    type="file"
                    accept=".zip"
                    hidden
                    onChange={(e) => handleFile(e.target.files?.[0])}
                />
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" strokeWidth="1.5" style={{ marginBottom: 14 }}>
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                    <path d="M17 8l-5-5-5 5" />
                    <path d="M12 3v12" />
                </svg>
                <div style={{ fontSize: 15, color: 'var(--text)' }}>
                    {loading ? 'Membaca & menyederhanakan file SHP...' : (<><span style={{ color: 'var(--primary)', fontWeight: 600 }}>Klik atau Upload</span> file SHP di sini</>)}
                </div>
                <div style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 6 }}>Arsip .zip berisi .shp, .dbf, .shx, .prj (ArcGIS Shapefile)</div>
                <div style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 4 }}>Polygon detail tinggi tetap bisa diunggah — otomatis disederhanakan saat dibaca</div>
            </div>
            {error && <p className="form-error">{error}</p>}
        </div>
    );
}
