<?php

namespace App\Support;

use JsonSerializable;

/**
 * Hands an already-shaped value straight to json_encode, hidden from Inertia's
 * prop resolver.
 *
 * The resolver walks every nested array in the prop tree looking for lazy/
 * deferred props, building a dot-path string for each node as it goes. On a
 * boundary polygon that is tens of thousands of [lng, lat] pairs deep that walk
 * alone costs double-digit seconds per request, even though none of those nodes
 * can ever be a lazy prop. A JsonSerializable object is not an array, so the
 * resolver passes it through untouched — and it serializes to exactly the same
 * JSON on the wire, so the page component sees no difference.
 */
class RawJson implements JsonSerializable
{
    public function __construct(private readonly mixed $value) {}

    /** Null stays null so `prop ?? fallback` keeps working on the client. */
    public static function wrap(mixed $value): ?self
    {
        return $value === null ? null : new self($value);
    }

    public function jsonSerialize(): mixed
    {
        return $this->value;
    }
}
