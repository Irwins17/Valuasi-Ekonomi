import assert from 'node:assert/strict';
import test from 'node:test';
import { simplifyToBudget } from '../../resources/js/lib/geo.js';

function circleRing(points = 100, offset = 0) {
    const ring = Array.from({ length: points }, (_, index) => {
        const angle = ((index + offset) % points) * Math.PI * 2 / points;

        return [110 + Math.cos(angle), -6 + Math.sin(angle)];
    });

    return [...ring, ring[0]];
}

test('dense closed polygons do not collapse into triangles', () => {
    const geojson = {
        type: 'FeatureCollection',
        features: Array.from({ length: 20 }, (_, index) => ({
            type: 'Feature',
            properties: { index },
            geometry: {
                type: 'Polygon',
                coordinates: [circleRing(100, index)],
            },
        })),
    };

    const result = simplifyToBudget(geojson, 140);
    const rings = result.geojson.features.map((feature) => feature.geometry.coordinates[0]);

    assert.equal(result.simplified, true);
    assert.ok(rings.every((ring) => ring.length >= 13));
    rings.forEach((ring) => assert.deepEqual(ring[0], ring.at(-1)));
});

test('small valid rings stay valid under a very coarse budget', () => {
    const ring = [[0, 0], [2, 0], [3, 1], [2, 2], [0, 2], [-1, 1], [0, 0]];
    const geojson = {
        type: 'FeatureCollection',
        features: Array.from({ length: 10 }, () => ({
            type: 'Feature',
            properties: {},
            geometry: { type: 'Polygon', coordinates: [ring] },
        })),
    };

    const result = simplifyToBudget(geojson, 10);

    result.geojson.features.forEach((feature) => {
        assert.equal(feature.geometry.coordinates[0].length, 7);
        assert.deepEqual(feature.geometry.coordinates[0][0], feature.geometry.coordinates[0].at(-1));
    });
});
