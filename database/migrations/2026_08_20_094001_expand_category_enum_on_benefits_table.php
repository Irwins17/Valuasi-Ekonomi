<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Flowchart Langkah 7 needs all 5 TEV value types as top-level
     * categories — TEV = DUV + IUV + OV + EV + BV — not the old 3-bucket
     * `direct_use/indirect_use/non_use` split, which left "Option Value"
     * completely unrepresented and buried Existence/Bequest Value as mere
     * subcategories of "non_use".
     *
     * Existing `non_use` rows are backfilled by their subcategory before the
     * column is narrowed, so no data is lost: `bequest_value` subcategory
     * rows become category `bequest_value`; everything else under `non_use`
     * (mostly `existence_value`, but also any stray subcategory — there was
     * no DB constraint tying the two together) becomes `existence_value`,
     * since that is the more general non-use motivation.
     *
     * Uses the fluent ->change() builder rather than raw SQL so this runs on
     * both MySQL (production) and SQLite (the test suite's in-memory DB).
     */
    private const OLD_VALUES = ['direct_use', 'indirect_use', 'non_use'];

    private const TRANSITIONAL_VALUES = ['direct_use', 'indirect_use', 'non_use', 'option_value', 'existence_value', 'bequest_value'];

    private const NEW_VALUES = ['direct_use', 'indirect_use', 'option_value', 'existence_value', 'bequest_value'];

    public function up(): void
    {
        // Widen first so the backfill below can write the new values while
        // "non_use" is still a valid value for the rows not yet updated —
        // narrowing before backfilling would reject every write to it.
        Schema::table('benefits', function (Blueprint $table) {
            $table->enum('category', self::TRANSITIONAL_VALUES)->change();
        });

        DB::table('benefits')
            ->where('category', 'non_use')
            ->where('subcategory', 'bequest_value')
            ->update(['category' => 'bequest_value']);

        DB::table('benefits')
            ->where('category', 'non_use')
            ->update(['category' => 'existence_value']);

        Schema::table('benefits', function (Blueprint $table) {
            $table->enum('category', self::NEW_VALUES)->change();
        });
    }

    public function down(): void
    {
        Schema::table('benefits', function (Blueprint $table) {
            $table->enum('category', self::TRANSITIONAL_VALUES)->change();
        });

        DB::table('benefits')->whereIn('category', ['option_value', 'existence_value', 'bequest_value'])
            ->update(['category' => 'non_use']);

        Schema::table('benefits', function (Blueprint $table) {
            $table->enum('category', self::OLD_VALUES)->change();
        });
    }
};
