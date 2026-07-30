import { useRef } from 'react';

function digitsOnly(str) {
    return String(str || '').replace(/[^0-9]/g, '');
}

function groupThousands(digits) {
    return digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
}

/**
 * Currency input matching the old `.rupiah-input` behavior: displays live
 * thousands-grouped digits (e.g. 50.000), but reports the raw numeric value
 * to onChange so the rest of the form keeps working with plain numbers.
 */
export default function CurrencyInput({ value, onChange, className = 'form-input', placeholder, required }) {
    const inputRef = useRef(null);
    const display = groupThousands(digitsOnly(value));

    function handleChange(e) {
        const el = e.target;
        const digitsBeforeCursor = digitsOnly(el.value.slice(0, el.selectionStart)).length;
        const rawDigits = digitsOnly(el.value);

        onChange(rawDigits === '' ? '' : rawDigits);

        requestAnimationFrame(() => {
            if (!inputRef.current) return;
            const grouped = groupThousands(rawDigits);
            let pos = 0;
            let seen = 0;
            while (pos < grouped.length && seen < digitsBeforeCursor) {
                if (/\d/.test(grouped[pos])) seen++;
                pos++;
            }
            inputRef.current.setSelectionRange(pos, pos);
        });
    }

    return (
        <input
            ref={inputRef}
            type="text"
            inputMode="numeric"
            autoComplete="off"
            className={className}
            placeholder={placeholder}
            required={required}
            value={display}
            onChange={handleChange}
        />
    );
}
