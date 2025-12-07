<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add GIN index for fast similarity searches
        DB::statement('CREATE INDEX tags_name_trgm_idx ON tags USING GIN (name gin_trgm_ops);');

        // Add index for case-insensitive searches
        DB::statement('CREATE INDEX tags_name_lower_idx ON tags (LOWER(name));');
    }

    public function down()
    {
        DB::statement('DROP INDEX IF EXISTS tags_name_trgm_idx;');
        DB::statement('DROP INDEX IF EXISTS tags_name_lower_idx;');
    }
};
