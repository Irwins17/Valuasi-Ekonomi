import assert from 'node:assert/strict';
import test from 'node:test';
import { applyLayerStyle, buildLegend } from '../../resources/js/lib/shpStyle.js';

test('legend exposes every SHP class instead of grouping overflow as lainnya', () => {
    const classCount = 17;
    const geojson = {
        type: 'FeatureCollection',
        features: Array.from({ length: classCount }, (_, index) => ({
            type: 'Feature',
            properties: { kelas: `Kelas ${index + 1}` },
            geometry: null,
        })),
    };

    applyLayerStyle(geojson);
    const legend = buildLegend(geojson);

    assert.equal(legend.length, classCount);
    assert.deepEqual(
        legend.map((entry) => entry.label),
        Array.from({ length: classCount }, (_, index) => `Kelas ${index + 1}`),
    );
    assert.ok(legend.every((entry) => entry.count === 1));
    assert.equal(new Set(legend.map((entry) => entry.color)).size, classCount);
});
