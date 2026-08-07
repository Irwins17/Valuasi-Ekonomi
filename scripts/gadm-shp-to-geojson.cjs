/**
 * Converts GADM's full-resolution Indonesia shapefiles into per-level
 * GeoJSON files that `php artisan boundaries:import --dir=` can read.
 *
 * GADM publishes two flavours of the same boundaries: a pre-simplified
 * GeoJSON (tiny, but a desa is reduced to ~14 vertices, which renders as a
 * blocky quadrilateral) and the full-resolution shapefile. This script uses
 * the latter, then applies a light Douglas-Peucker pass so coastlines and
 * river edges stay detailed while redundant collinear vertices are dropped.
 *
 * Usage:
 *   node scripts/gadm-shp-to-geojson.cjs <extractedShpDir> <outDir> [levels...]
 */
const fs = require('fs');
const path = require('path');
const shp = require('shpjs');

// ~1.1 m at the equator. Small enough to preserve the shape of a village
// boundary, large enough to drop vertices that add bytes but no visible
// detail at any zoom the admin map supports.
const TOLERANCE = 0.00001;

function perpendicularDistance(p, a, b) {
    const [px, py] = p;
    const [ax, ay] = a;
    const [bx, by] = b;
    const dx = bx - ax;
    const dy = by - ay;
    if (dx === 0 && dy === 0) return Math.hypot(px - ax, py - ay);
    const t = ((px - ax) * dx + (py - ay) * dy) / (dx * dx + dy * dy);
    const cx = ax + Math.max(0, Math.min(1, t)) * dx;
    const cy = ay + Math.max(0, Math.min(1, t)) * dy;
    return Math.hypot(px - cx, py - cy);
}

function simplifyRing(points, tolerance) {
    if (points.length <= 4) return points;

    // Iterative Douglas-Peucker; recursion blows the stack on rings with
    // tens of thousands of vertices (coastal regencies do hit that).
    const keep = new Uint8Array(points.length);
    keep[0] = 1;
    keep[points.length - 1] = 1;
    const stack = [[0, points.length - 1]];

    while (stack.length) {
        const [first, last] = stack.pop();
        let maxDist = 0;
        let index = -1;
        for (let i = first + 1; i < last; i++) {
            const d = perpendicularDistance(points[i], points[first], points[last]);
            if (d > maxDist) {
                maxDist = d;
                index = i;
            }
        }
        if (maxDist > tolerance && index !== -1) {
            keep[index] = 1;
            stack.push([first, index], [index, last]);
        }
    }

    const out = [];
    for (let i = 0; i < points.length; i++) if (keep[i]) out.push(points[i]);

    // A polygon ring needs at least 4 positions (closed triangle); if
    // simplification collapsed it, keep the original rather than emit
    // invalid geometry.
    if (out.length < 4) return points;
    if (out[0][0] !== out[out.length - 1][0] || out[0][1] !== out[out.length - 1][1]) {
        out.push(out[0]);
    }
    return out;
}

function simplifyGeometry(geometry) {
    if (!geometry) return geometry;
    if (geometry.type === 'Polygon') {
        return { type: 'Polygon', coordinates: geometry.coordinates.map((r) => simplifyRing(r, TOLERANCE)) };
    }
    if (geometry.type === 'MultiPolygon') {
        return {
            type: 'MultiPolygon',
            coordinates: geometry.coordinates.map((poly) => poly.map((r) => simplifyRing(r, TOLERANCE))),
        };
    }
    return geometry;
}

function countPoints(geometry) {
    let n = 0;
    const walk = (c) => {
        if (typeof c[0] === 'number') { n++; return; }
        c.forEach(walk);
    };
    if (geometry && geometry.coordinates) walk(geometry.coordinates);
    return n;
}

async function convertLevel(shpDir, outDir, level) {
    const base = path.join(shpDir, `gadm41_IDN_${level}`);
    if (!fs.existsSync(`${base}.shp`)) {
        console.log(`level ${level}: ${base}.shp not found, skipping`);
        return;
    }

    console.log(`level ${level}: reading shapefile ...`);
    const geojson = await shp({
        shp: fs.readFileSync(`${base}.shp`).buffer,
        dbf: fs.readFileSync(`${base}.dbf`).buffer,
        prj: fs.existsSync(`${base}.prj`) ? fs.readFileSync(`${base}.prj`, 'utf8') : undefined,
    });

    const features = geojson.features || [];
    let before = 0;
    let after = 0;

    for (const f of features) {
        before += countPoints(f.geometry);
        f.geometry = simplifyGeometry(f.geometry);
        after += countPoints(f.geometry);
    }

    const outPath = path.join(outDir, `gadm41_IDN_${level}.json`);
    fs.writeFileSync(outPath, JSON.stringify({ type: 'FeatureCollection', features }));
    const mb = (fs.statSync(outPath).size / 1024 / 1024).toFixed(1);
    console.log(
        `level ${level}: ${features.length} features | pts ${before} -> ${after}` +
        ` (${((1 - after / before) * 100).toFixed(1)}% dropped) | ${mb} MB -> ${outPath}`
    );
}

(async () => {
    const [shpDir, outDir, ...levelArgs] = process.argv.slice(2);
    if (!shpDir || !outDir) {
        console.error('Usage: node scripts/gadm-shp-to-geojson.cjs <extractedShpDir> <outDir> [levels...]');
        process.exit(1);
    }
    fs.mkdirSync(outDir, { recursive: true });
    const levels = levelArgs.length ? levelArgs.map(Number) : [1, 2, 3, 4];
    for (const level of levels) {
        await convertLevel(shpDir, outDir, level);
    }
})().catch((e) => { console.error('FAILED', e); process.exit(1); });
