import { toNumber } from './format';

/**
 * PV = Nilai / (1 + r)ⁿ, n = tahun − tahun dasar.
 *
 * A browser-side mirror of EconomicValuationCalculator::calculatePV(), used
 * only to preview what a form is about to save. The calculator remains the
 * single authority: nothing here is ever submitted, the server recomputes
 * pv_value on every write. The floor at n = 0 is copied deliberately — an
 * amount dated before the base year keeps its face value instead of being
 * compounded upward, and a preview that disagreed with that would be worse
 * than no preview at all.
 */
export function presentValue(value, year, baseYear, discountRate) {
    const amount = toNumber(value);
    const base = toNumber(baseYear);
    const n = Math.max(0, toNumber(year, base) - base);

    if (n === 0) return amount;

    return amount / (1 + toNumber(discountRate) / 100) ** n;
}
