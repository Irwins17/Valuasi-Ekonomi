/**
 * Streams the HDX "Indonesia - Subnational Administrative Boundaries"
 * GeoJSON files (BPS / OCHA ROAP, CC BY-IGO) into compact NDJSON that
 * `php artisan boundaries:import` can ingest line by line.
 *
 * Why streaming: the desa layer alone is a 620 MB single-line
 * FeatureCollection, so neither Node nor PHP can json_decode it whole. This
 * scans the file with a brace/quote-aware reader and hands off one feature
 * at a time.
 *
 * Why simplification: the source keeps ~11x more vertices than any zoom the
 * admin map offers can show. A light Douglas-Peucker pass keeps the visible
 * shape (coastlines, river bends) while cutting storage and render cost.
 *
 * Usage:
 *   node scripts/hdx-boundaries-to-ndjson.cjs <hdxDir> <outDir> [levels...]
 */
const fs = require('fs');
const path = require('path');

// Douglas-Peucker tolerance in degrees, per level (~111 km per degree).
// Detail is matched to the zoom each level is actually viewed at: the map
// fits the whole selected area, so a province fills the viewport at ~100 km
// across while a desa fills it at ~2 km across. Using one fine tolerance
// everywhere would store hundreds of vertices per rendered pixel on the
// larger areas for no visible gain.
const TOLERANCE_BY_LEVEL = {
    1: 0.0004,   // provinsi        ~45 m
    2: 0.00015,  // kabupaten/kota  ~17 m
    3: 0.00006,  // kecamatan       ~7 m
    4: 0.00002,  // kelurahan/desa  ~2 m
};

function perpendicularDistance(p, a, b) {
    const dx = b[0] - a[0];
    const dy = b[1] - a[1];
    if (dx === 0 && dy === 0) return Math.hypot(p[0] - a[0], p[1] - a[1]);
    let t = ((p[0] - a[0]) * dx + (p[1] - a[1]) * dy) / (dx * dx + dy * dy);
    t = t < 0 ? 0 : t > 1 ? 1 : t;
    return Math.hypot(p[0] - (a[0] + t * dx), p[1] - (a[1] + t * dy));
}

function simplifyRing(points, tolerance) {
    if (points.length <= 4) return points;
    const keep = new Uint8Array(points.length);
    keep[0] = 1;
    keep[points.length - 1] = 1;
    // Iterative, not recursive: coastal regencies have rings with >100k
    // vertices and recursion overflows the stack.
    const stack = [[0, points.length - 1]];
    while (stack.length) {
        const [first, last] = stack.pop();
        let maxDist = 0;
        let index = -1;
        for (let i = first + 1; i < last; i++) {
            const d = perpendicularDistance(points[i], points[first], points[last]);
            if (d > maxDist) { maxDist = d; index = i; }
        }
        if (maxDist > tolerance && index !== -1) {
            keep[index] = 1;
            stack.push([first, index], [index, last]);
        }
    }
    const out = [];
    for (let i = 0; i < points.length; i++) if (keep[i]) out.push(points[i]);
    if (out.length < 4) return points;
    const f = out[0];
    const l = out[out.length - 1];
    if (f[0] !== l[0] || f[1] !== l[1]) out.push(f);
    return out;
}

function simplifyGeometry(geometry, TOLERANCE) {
    if (!geometry) return null;
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

function statsAndBbox(geometry) {
    let n = 0;
    let minLat = Infinity, minLng = Infinity, maxLat = -Infinity, maxLng = -Infinity;
    const walk = (c) => {
        if (typeof c[0] === 'number') {
            n++;
            const [lng, lat] = c;
            if (lat < minLat) minLat = lat;
            if (lat > maxLat) maxLat = lat;
            if (lng < minLng) minLng = lng;
            if (lng > maxLng) maxLng = lng;
            return;
        }
        for (const x of c) walk(x);
    };
    if (geometry && geometry.coordinates) walk(geometry.coordinates);
    return { n, minLat, minLng, maxLat, maxLng };
}

const stripId = (pcode) => (typeof pcode === 'string' ? pcode.replace(/^ID/i, '').trim() || null : null);

/**
 * Calls onFeature for each top-level Feature object, without ever holding
 * more than one feature in memory.
 */
function streamFeatures(filePath, onFeature) {
    return new Promise((resolve, reject) => {
        const stream = fs.createReadStream(filePath, { encoding: 'utf8', highWaterMark: 1 << 20 });
        let buf = '';
        let scanned = 0;   // chars of `buf` already examined
        let depth = 0;
        let start = -1;    // index in `buf` where the current feature began
        let inString = false;
        let escaped = false;
        let started = false;

        stream.on('data', (chunk) => {
            buf += chunk;
            // Resume where the previous chunk left off; rescanning from 0
            // would replay characters and corrupt depth/inString state.
            for (let i = scanned; i < buf.length; i++) {
                const ch = buf[i];
                if (inString) {
                    if (escaped) escaped = false;
                    else if (ch === '\\') escaped = true;
                    else if (ch === '"') inString = false;
                    continue;
                }
                if (ch === '"') { inString = true; continue; }
                if (!started) { if (ch === '[') started = true; continue; }
                if (ch === '{') { if (depth === 0) start = i; depth++; continue; }
                if (ch === '}') {
                    depth--;
                    if (depth === 0 && start !== -1) {
                        onFeature(JSON.parse(buf.slice(start, i + 1)));
                        // Drop everything consumed so far and restart the
                        // scan at the head of the trimmed buffer.
                        buf = buf.slice(i + 1);
                        i = -1;
                        scanned = 0;
                        start = -1;
                    }
                }
            }
            scanned = buf.length;
        });
        stream.on('end', resolve);
        stream.on('error', reject);
    });
}

async function convertLevel(hdxDir, outDir, level) {
    const src = path.join(hdxDir, `idn_admin${level}.geojson`);
    if (!fs.existsSync(src)) { console.log(`level ${level}: ${src} missing, skipping`); return; }

    const tolerance = TOLERANCE_BY_LEVEL[level];
    const outPath = path.join(outDir, `boundaries_level${level}.ndjson`);
    const out = fs.createWriteStream(outPath);
    let count = 0, skipped = 0, ptsBefore = 0, ptsAfter = 0;

    await streamFeatures(src, (f) => {
        const p = f.properties || {};
        const code = stripId(p[`adm${level}_pcode`]);
        if (!code || !f.geometry) { skipped++; return; }

        ptsBefore += statsAndBbox(f.geometry).n;
        const geometry = simplifyGeometry(f.geometry, tolerance);
        const s = statsAndBbox(geometry);
        ptsAfter += s.n;
        if (!isFinite(s.minLat)) { skipped++; return; }

        out.write(JSON.stringify({
            code,
            name: p[`adm${level}_name`] ?? p[`adm${level}_ref_name`] ?? '',
            parent_code: level > 1 ? stripId(p[`adm${level - 1}_pcode`]) : null,
            province_name: p.adm1_name ?? null,
            geojson: geometry,
            min_lat: s.minLat, min_lng: s.minLng, max_lat: s.maxLat, max_lng: s.maxLng,
        }) + '\n');
        count++;
    });

    await new Promise((r) => out.end(r));
    const mb = (fs.statSync(outPath).size / 1024 / 1024).toFixed(1);
    console.log(
        `level ${level}: ${count} features (${skipped} skipped) | pts ${ptsBefore} -> ${ptsAfter}` +
        ` (${ptsBefore ? ((1 - ptsAfter / ptsBefore) * 100).toFixed(1) : 0}% dropped) | ${mb} MB -> ${outPath}`
    );
}

(async () => {
    const [hdxDir, outDir, ...levelArgs] = process.argv.slice(2);
    if (!hdxDir || !outDir) {
        console.error('Usage: node scripts/hdx-boundaries-to-ndjson.cjs <hdxDir> <outDir> [levels...]');
        process.exit(1);
    }
    fs.mkdirSync(outDir, { recursive: true });
    for (const level of (levelArgs.length ? levelArgs.map(Number) : [1, 2, 3, 4])) {
        await convertLevel(hdxDir, outDir, level);
    }
})().catch((e) => { console.error('FAILED', e); process.exit(1); });
