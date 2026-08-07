import { useRef, useState } from 'react';
import shp from 'shpjs';
import ProjectLocationMap from './ProjectLocationMap';
import { boundaryCentroid, countFeatures, countCoordinates, findInvalidCoordinate } from '../../lib/geo';

// Above this, a boundary stops being a lightweight preview polygon and
// starts being a multi-megabyte blob that has to round-trip through every
// page that lists the project (dashboard, project index, ...). A real
// high-detail administrative boundary can still clear this comfortably
// after simplifying in GIS software before export.
const MAX_COORDINATES = 20000;

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
export default function ShpUploader({ value = null, onParsed, onClear, height = 220 }) {
    const inputRef = useRef(null);
    const [dragOver, setDragOver] = useState(false);
    const [loading, setLoading] = useState(false);
    const [fileName, setFileName] = useState(null);
    const [error, setError] = useState(null);

    async function handleFile(file) {
        if (!file) return;
        if (!file.name.toLowerCase().endsWith('.zip')) {
            setError('File harus berupa arsip .zip berisi .shp, .dbf, .shx, dan .prj');
            return;
        }

        setLoading(true);
        setError(null);
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
            if (pointCount > MAX_COORDINATES) {
                throw new Error(`File terlalu detail (${pointCount.toLocaleString('id-ID')} titik koordinat, maksimum ${MAX_COORDINATES.toLocaleString('id-ID')}). Sederhanakan polygon di software GIS (mis. "Simplify Geometry" di QGIS/ArcGIS) sebelum diunggah.`);
            }

            const centroid = boundaryCentroid(geojson);
            if (!centroid) {
                throw new Error('Gagal membaca koordinat dari file SHP');
            }

            setFileName(file.name);
            onParsed?.(geojson, centroid);
        } catch (err) {
            setError(err.message || 'Gagal membaca file SHP');
        } finally {
            setLoading(false);
        }
    }

    function clear() {
        setFileName(null);
        setError(null);
        if (inputRef.current) inputRef.current.value = '';
        onClear?.();
    }

    if (value) {
        return (
            <div>
                <ProjectLocationMap boundary={value} height={height} style={{ borderRadius: 'var(--radius-sm)', border: '1px solid var(--border)', overflow: 'hidden' }} />
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: 8 }}>
                    <span style={{ fontSize: 12, color: 'var(--text-muted)' }}>
                        {fileName ? `${fileName} — ` : ''}{countFeatures(value)} fitur polygon berhasil dibaca
                    </span>
                    <button type="button" className="btn btn-sm btn-ghost" onClick={clear}>Ganti file</button>
                </div>
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
                    {loading ? 'Membaca file SHP...' : (<><span style={{ color: 'var(--primary)', fontWeight: 600 }}>Klik atau Upload</span> file SHP di sini</>)}
                </div>
                <div style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 6 }}>Arsip .zip berisi .shp, .dbf, .shx, .prj (ArcGIS Shapefile)</div>
            </div>
            {error && <p className="form-error">{error}</p>}
        </div>
    );
}
