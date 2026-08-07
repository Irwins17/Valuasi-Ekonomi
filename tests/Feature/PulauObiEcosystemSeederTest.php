<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Services\Valuation\EcosystemServiceValuationCalculator;
use Database\Seeders\PulauObiEcosystemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cross-checks the transcribed seed data against the subtotals printed in
 * "Bahan Sistem Informasi VALEK" (Lampiran 1-4), so a transposition typo in
 * ~170 hand-copied rows doesn't silently slip through.
 */
class PulauObiEcosystemSeederTest extends TestCase
{
    use RefreshDatabase;

    private EcosystemServiceValuationCalculator $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new EcosystemServiceValuationCalculator;

        $role = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
        User::create([
            'name' => 'Admin Test', 'email' => 'admin-test@valuasi.local',
            'password' => 'password123', 'role_id' => $role->id, 'is_active' => true,
        ]);

        (new PulauObiEcosystemSeeder)->run();
    }

    private function landCoverTotal(Project $project, int $indexNumber, string $landCoverName): float
    {
        $index = $project->ecosystemValuationIndices()->where('index_number', $indexNumber)->firstOrFail();
        $landCover = $index->landCovers()->where('name', $landCoverName)->firstOrFail();

        return $this->calc->summarize($landCover->items()->get(['service_category', 'total_value'])->toArray())['total'];
    }

    public function test_index1_reklamasi_matches_pdf_subtotals(): void
    {
        $project = Project::where('code', 'PROJ-008')->firstOrFail();
        $index = $project->ecosystemValuationIndices()->where('index_number', 1)->firstOrFail();
        $landCover = $index->landCovers()->where('name', 'Area Reklamasi')->firstOrFail();
        $summary = $this->calc->summarize($landCover->items()->get(['service_category', 'total_value'])->toArray());

        $this->assertEqualsWithDelta(22_223_499_556, $summary['by_category']['provisioning'], 5000);
        $this->assertEqualsWithDelta(7_404_828_952, $summary['by_category']['regulating'], 5000);
        $this->assertEqualsWithDelta(4_932_819_695, $summary['by_category']['supporting'], 5000);
        $this->assertEqualsWithDelta(34_561_148_203, $summary['total'], 15000);
    }

    public function test_index1_land_cover_tevs_match_pdf(): void
    {
        $project = Project::where('code', 'PROJ-008')->firstOrFail();

        $this->assertEqualsWithDelta(480_219_237, $this->landCoverTotal($project, 1, 'Area Belukar'), 2000);
        $this->assertEqualsWithDelta(22_642_935_450, $this->landCoverTotal($project, 1, 'Semak Belukar'), 10000);
        $this->assertEqualsWithDelta(1_926_152_971, $this->landCoverTotal($project, 1, 'Lahan Terbangun'), 2000);
    }

    public function test_index2_land_cover_tevs_match_pdf(): void
    {
        $project = Project::where('code', 'PROJ-008')->firstOrFail();

        $this->assertEqualsWithDelta(654_370_202, $this->landCoverTotal($project, 2, 'Area Reklamasi'), 2000);
        $this->assertEqualsWithDelta(186_131_487, $this->landCoverTotal($project, 2, 'Area Belukar'), 2000);
        $this->assertEqualsWithDelta(22_080_137_762, $this->landCoverTotal($project, 2, 'Semak Belukar'), 10000);
        $this->assertEqualsWithDelta(512_926_268, $this->landCoverTotal($project, 2, 'Lahan Terbangun'), 2000);
    }

    public function test_index3_land_cover_tevs_match_pdf(): void
    {
        $project = Project::where('code', 'PROJ-008')->firstOrFail();

        $this->assertEqualsWithDelta(11_984_126_808, $this->landCoverTotal($project, 3, 'Area Belukar'), 10000);
        $this->assertEqualsWithDelta(45_084_096_962, $this->landCoverTotal($project, 3, 'Hutan Lahan Kering Sekunder'), 20000);
        $this->assertEqualsWithDelta(46_447_983_391, $this->landCoverTotal($project, 3, 'Semak Belukar'), 20000);
    }

    public function test_cultural_services_total_is_identical_across_indices(): void
    {
        $project = Project::where('code', 'PROJ-008')->firstOrFail();

        foreach ([1, 2, 3, 4] as $indexNumber) {
            $index = $project->ecosystemValuationIndices()->where('index_number', $indexNumber)->firstOrFail();
            $total = $this->calc->summarize(
                $index->items()->whereNull('land_cover_id')->get(['service_category', 'total_value'])->toArray()
            )['total'];

            $this->assertEqualsWithDelta(2_416_872_220, $total, 100, "Cultural total mismatch on index {$indexNumber}");
        }
    }

    public function test_index4_incomplete_land_covers_have_no_area_and_no_items(): void
    {
        $project = Project::where('code', 'PROJ-008')->firstOrFail();
        $index = $project->ecosystemValuationIndices()->where('index_number', 4)->firstOrFail();

        foreach (['Area Belukar', 'Semak Belukar'] as $name) {
            $landCover = $index->landCovers()->where('name', $name)->firstOrFail();
            $this->assertNull($landCover->area_ha);
            $this->assertCount(0, $landCover->items);
            $this->assertNotNull($landCover->notes);
        }

        $lahanTerbangun = $index->landCovers()->where('name', 'Lahan Terbangun')->firstOrFail();
        $this->assertEqualsWithDelta(345.29, (float) $lahanTerbangun->area_ha, 0.01);
        $this->assertCount(2, $lahanTerbangun->items);
    }
}
