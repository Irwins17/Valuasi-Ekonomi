import { useEffect, useRef, useState } from 'react';
import ProjectLocationMap from './ProjectLocationMap';
import { PROVINCES } from '../../lib/provinces';
import {PROVINCE_EMSIFA_ID, PROVINCE_BOUNDARY_CODE,PROVINCE_REGENCY_IDS,fetchRegencies,fetchDistricts,fetchVillages,} from '../../lib/wilayah';
import { boundaryCentroid, findInvalidCoordinate, roundCoordinatePrecision, simplifyToBudget } from '../../lib/geo';

// Same budget the SHP uploader uses: anything denser is thinned client-side
// rather than refused, so a high-detail kelurahan/kabupaten boundary still
// draws instead of pushing the user to a manual upload.
const MAX_COORDINATES = 60000;

export default function AdministrativeBoundaryPicker({ province, onProvinceChange, value, onFound, onReset, height = 560 }) {
const [regencies, setRegencies] = useState([]);
    const [districts, setDistricts] = useState([]);
    const [villages, setVillages] = useState([]);
    const [regency, setRegency] = useState(null);
    const [district, setDistrict] = useState(null);
    const [village, setVillage] = useState(null);
    const [loadingList, setLoadingList] = useState(false);
    const [loadingBoundary, setLoadingBoundary] = useState(false);
    const [notice, setNotice] = useState(null);
    const requestId = useRef(0);
    const isFirstRun = useRef(true);

    useEffect(() => {
        setRegency(null);
        setDistrict(null);
        setVillage(null);
        setRegencies([]);
        setDistricts([]);
        setVillages([]);
        setNotice(null);

        const skipAutoDraw = isFirstRun.current && !!value;
        isFirstRun.current = false;

        if (!province) return;

        const emsifaId = PROVINCE_EMSIFA_ID[province];
        if (!emsifaId) return;
        

        if (!skipAutoDraw) {
            lookupBoundary(1, PROVINCE_BOUNDARY_CODE[province] ?? emsifaId, province);
        }

        const keep = PROVINCE_REGENCY_IDS[province];

        setLoadingList(true);
        fetchRegencies(emsifaId)
            .then((rows) => setRegencies(keep ? rows.filter((r) => keep.includes(r.id)) : rows))
            .catch(() => setNotice({ type: 'error', text: 'Gagal memuat daftar kabupaten/kota.' }))
            .finally(() => setLoadingList(false));
    }, [province]);

    async function lookupBoundary(level, code, label) {
        const myRequestId = ++requestId.current;
        setLoadingBoundary(true);
        setNotice(null);
        try {
            const res = await fetch(route('admin.boundary.lookup', { level, code }));
            const data = await res.json();
            if (myRequestId !== requestId.current) return;
            if (!data.found) {
                setNotice({ type: 'warning', text: `Batas poligon untuk "${label}" tidak tersedia di basis data wilayah — peta tetap menampilkan batas level di atasnya, tapi Lokasi sudah diperbarui ke pilihan Anda. Unggah file SHP manual untuk polygon yang presisi.` });

                if (value) {
                    const centroid = boundaryCentroid(value);
                    if (centroid) onFound?.(value, centroid, label);
                }
                return;
            }

            const invalid = findInvalidCoordinate(data.boundary);
            if (invalid) {
                setNotice({ type: 'warning', text: 'Batas wilayah untuk lokasi ini tidak valid (koordinat di luar jangkauan). Gunakan upload SHP manual.' });
                return;
            }

            const { geojson, simplified, before, after } = simplifyToBudget(data.boundary, MAX_COORDINATES);
            if (simplified) {
                setNotice({
                    type: 'info',
                    text: `Batas wilayah sangat detail (${before.toLocaleString('id-ID')} titik) dan disederhanakan otomatis menjadi ${after.toLocaleString('id-ID')} titik agar ringan digambar.`,
                });
            }

            onFound?.(roundCoordinatePrecision(geojson), data.center, label);
        } catch (err) {
            if (myRequestId !== requestId.current) return;
            setNotice({ type: 'error', text: 'Gagal mengambil batas wilayah.' });
        } finally {
            if (myRequestId === requestId.current) setLoadingBoundary(false);
        }
    }

    function drawProvince() {
        if (!province) return;
        lookupBoundary(1, PROVINCE_BOUNDARY_CODE[province] ?? PROVINCE_EMSIFA_ID[province], province);
    }

    function handleRegencyChange(id) {
        const r = regencies.find((x) => x.id === id) || null;
        setRegency(r);
        setDistrict(null);
        setVillage(null);
        setDistricts([]);
        setVillages([]);
        if (!r) {
            drawProvince();
            return;
        }

        setLoadingList(true);
        fetchDistricts(r.id)
            .then(setDistricts)
            .catch(() => setNotice({ type: 'error', text: 'Gagal memuat daftar kecamatan.' }))
            .finally(() => setLoadingList(false));

        lookupBoundary(2, r.id, r.name);
    }

    function handleDistrictChange(id) {
        const d = districts.find((x) => x.id === id) || null;
        setDistrict(d);
        setVillage(null);
        setVillages([]);
        if (!d) {
            if (regency) lookupBoundary(2, regency.id, regency.name);
            else drawProvince();
            return;
        }

        setLoadingList(true);
        fetchVillages(d.id)
            .then(setVillages)
            .catch(() => setNotice({ type: 'error', text: 'Gagal memuat daftar kelurahan/desa.' }))
            .finally(() => setLoadingList(false));

        lookupBoundary(3, d.id, `Kecamatan ${d.name}, ${regency.name}`);
    }

    function handleVillageChange(id) {
        const v = villages.find((x) => x.id === id) || null;
        setVillage(v);
        if (!v) {
            if (district) lookupBoundary(3, district.id, `Kecamatan ${district.name}, ${regency.name}`);
            else if (regency) lookupBoundary(2, regency.id, regency.name);
            else drawProvince();
            return;
        }

        lookupBoundary(4, v.id, `${v.name}, Kecamatan ${district.name}, ${regency.name}`);
    }

    function reset() {
        setRegency(null);
        setDistrict(null);
        setVillage(null);
        setDistricts([]);
        setVillages([]);
        setNotice(null);
        onReset?.();
    }

    return (
        <div>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                <div className="form-group">
                    <label className="form-label">Provinsi</label>
                    <select value={province} onChange={(e) => onProvinceChange(e.target.value)} className="form-input">
                        <option value="">— Pilih provinsi —</option>
                        {PROVINCES.map((p) => (
                            <option key={p} value={p}>{p}</option>
                        ))}
                    </select>
                </div>
                <div className="form-group">
                    <label className="form-label">Kabupaten/Kota</label>
                    <select value={regency?.id ?? ''} onChange={(e) => handleRegencyChange(e.target.value)} disabled={!province || loadingList} className="form-input">
                        <option value="">{loadingList && !regencies.length ? 'Memuat...' : '— Pilih kabupaten/kota —'}</option>
                        {regencies.map((r) => (
                            <option key={r.id} value={r.id}>{r.name}</option>
                        ))}
                    </select>
                </div>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                <div className="form-group">
                    <label className="form-label">Kecamatan</label>
                    <select value={district?.id ?? ''} onChange={(e) => handleDistrictChange(e.target.value)} disabled={!regency || loadingList} className="form-input">
                        <option value="">— Pilih kecamatan —</option>
                        {districts.map((d) => (
                            <option key={d.id} value={d.id}>{d.name}</option>
                        ))}
                    </select>
                </div>
                <div className="form-group">
                    <label className="form-label">Kelurahan/Desa</label>
                    <select value={village?.id ?? ''} onChange={(e) => handleVillageChange(e.target.value)} disabled={!district || loadingList} className="form-input">
                        <option value="">— Pilih kelurahan/desa —</option>
                        {villages.map((v) => (
                            <option key={v.id} value={v.id}>{v.name}</option>
                        ))}
                    </select>
                </div>
            </div>

            {loadingBoundary && <p className="form-hint">Mengambil batas wilayah...</p>}
            {notice && <p className={notice.type === 'error' ? 'form-error' : 'form-hint'} style={notice.type === 'warning' ? { color: 'var(--warning)' } : undefined}>{notice.text}</p>}

            {value ? (
                <div>
                    <ProjectLocationMap boundary={value} height={height} style={{ borderRadius: 'var(--radius-sm)', border: '1px solid var(--border)', overflow: 'hidden' }} />
                    <div style={{ display: 'flex', justifyContent: 'flex-end', marginTop: 8 }}>
                        <button type="button" className="btn btn-sm btn-ghost" onClick={reset}>Reset Pilihan</button>
                    </div>
                </div>
            ) : (
                <div style={{
                    height, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center',
                    textAlign: 'center', border: '1.5px dashed var(--border)', borderRadius: 'var(--radius-sm)', background: 'var(--surface-alt)',
                }}>
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" strokeWidth="1.5" style={{ marginBottom: 12 }}>
                        <path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    <div style={{ fontSize: 14, color: 'var(--text)' }}>Pilih wilayah di atas untuk menggambar batas area secara otomatis</div>
                </div>
            )}
        </div>
    );
}
