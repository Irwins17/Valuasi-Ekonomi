<?php

namespace App\Support;
class BoundaryGeometryStore
{
    
    public const PRECISION = 5;

    private const GZIP_LEVEL = 6;

    public static function baseDir(): string
    {
        return config('boundaries.geometry_path');
    }

    public static function path(int $level, string $code): string
    {
        $shard = strlen($code) > 4 ? '/'.substr($code, 0, 4) : '';

        return self::baseDir()."/{$level}{$shard}/{$code}.json.gz";
    }

    /**
     * @return int bytes written
     */
    public static function put(int $level, string $code, array $geometry): int
    {
        $path = self::path($level, $code);
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $payload = gzencode(json_encode(self::round($geometry)), self::GZIP_LEVEL);
        file_put_contents($path, $payload);

        return strlen($payload);
    }

    public static function get(int $level, string $code): ?array
    {
        $path = self::path($level, $code);
        if (! is_file($path)) {
            return null;
        }

        $raw = gzdecode(file_get_contents($path));
        if ($raw === false) {
            return null;
        }

        return json_decode($raw, true) ?: null;
    }

    public static function has(int $level, string $code): bool
    {
        return is_file(self::path($level, $code));
    }

    private static function round(array $geometry): array
    {
        if (isset($geometry['coordinates'])) {
            self::roundCoordinates($geometry['coordinates']);
        }

        return $geometry;
    }

    private static function roundCoordinates(array &$coords): void
    {
        if (isset($coords[0]) && is_numeric($coords[0]) && isset($coords[1]) && is_numeric($coords[1])) {
            $coords[0] = round($coords[0], self::PRECISION);
            $coords[1] = round($coords[1], self::PRECISION);

            return;
        }

        foreach ($coords as &$child) {
            if (is_array($child)) {
                self::roundCoordinates($child);
            }
        }
    }
}
