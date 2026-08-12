import { useState } from 'react';
import AdministrativeBoundaryPicker from './AdministrativeBoundaryPicker';
import ShpUploader from './ShpUploader';

const TABS = [
    { key: 'wilayah', label: 'Pilih Wilayah Administratif' },
    { key: 'shp', label: 'Upload File SHP' },
];

export default function BoundarySourcePicker({ value, onParsed, onClear, province, onProvinceChange, height = 420 }) {
    const [tab, setTab] = useState('wilayah');

    return (
        <div>
            <div style={{ display: 'flex', gap: 6, marginBottom: 12 }}>
                {TABS.map((t) => (
                    <button
                        key={t.key}
                        type="button"
                        className={`btn btn-sm ${tab === t.key ? 'btn-primary' : 'btn-outline'}`}
                        onClick={() => setTab(t.key)}
                    >
                        {t.label}
                    </button>
                ))}
            </div>

            {tab === 'wilayah' ? (
                <AdministrativeBoundaryPicker
                    province={province}
                    onProvinceChange={onProvinceChange}
                    value={value}
                    onFound={onParsed}
                    onReset={onClear}
                    height={height}
                />
            ) : (
                <ShpUploader value={value} onParsed={onParsed} onClear={onClear} height={height} />
            )}
        </div>
    );
}
