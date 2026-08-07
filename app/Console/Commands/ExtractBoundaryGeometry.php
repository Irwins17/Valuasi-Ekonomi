<?php

namespace App\Console\Commands;

use App\Support\BoundaryGeometryStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class ExtractBoundaryGeometry extends Command
{
    protected $signature = 'boundaries:extract-geometry';

    protected $description = 'Move boundary polygons from the legacy geojson column into the on-disk geometry store';

    public function handle(): int
    {
        if (! Schema::hasColumn('administrative_boundaries', 'geojson')) {
            $this->info('No legacy `geojson` column — geometry is already on disk. Nothing to do.');

            return self::SUCCESS;
        }

        ini_set('memory_limit', '1G');

        $total = DB::table('administrative_boundaries')->count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $written = 0;
        $bytes = 0;
        $skipped = 0;

        DB::table('administrative_boundaries')
            ->select('id', 'level', 'code', 'geojson')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use (&$written, &$bytes, &$skipped, $bar) {
                foreach ($rows as $row) {
                    $geometry = json_decode($row->geojson, true);

                    if (! is_array($geometry) || ! isset($geometry['coordinates'])) {
                        $skipped++;
                        $bar->advance();

                        continue;
                    }

                    $bytes += BoundaryGeometryStore::put($row->level, $row->code, $geometry);
                    $written++;
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);

        if ($skipped) {
            $this->warn("Skipped {$skipped} row(s) with unusable geometry.");
        }

        $this->info(sprintf(
            'Wrote %s geometry files, %.1f MB total, into %s',
            number_format($written),
            $bytes / 1048576,
            BoundaryGeometryStore::baseDir()
        ));
        $this->line('Now run: php artisan migrate  (drops the legacy column)');

        return self::SUCCESS;
    }
}
