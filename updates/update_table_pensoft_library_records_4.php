<?php namespace Pensoft\Library\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateTablePensoftLibraryRecords4 extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pensoft_library_records')) {
            Schema::table('pensoft_library_records', function($table)
            {
                $table->string('milestone_number')->nullable()->after('deliverable_number');
            });
        }
    }
    
    public function down()
    {
        if (Schema::hasTable('pensoft_library_records')) {
            Schema::table('pensoft_library_records', function($table)
            {
                $table->dropColumn('milestone_number');
            });
        }
    }
}
