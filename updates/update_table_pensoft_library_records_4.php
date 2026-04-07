<?php namespace Pensoft\Library\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UpdateTablePensoftLibraryRecords4 extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pensoft_library_records')) {
            Schema::table('pensoft_library_records', function(Blueprint $table)
            {
                $table->string('milestone_number')->nullable()->after('deliverable_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pensoft_library_records')) {
            Schema::table('pensoft_library_records', function(Blueprint $table)
            {
                $table->dropColumn('milestone_number');
            });
        }
    }
}