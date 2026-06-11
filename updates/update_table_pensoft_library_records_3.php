<?php namespace Pensoft\Library\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateTablePensoftLibraryRecords3 extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pensoft_library_records')) {
            Schema::table('pensoft_library_records', function($table)
            {
                $table->string('deliverable_number')->nullable()->after('deliverable_title');
            });
        }
    }
    
    public function down()
    {
        if (Schema::hasTable('pensoft_library_records')) {
            Schema::table('pensoft_library_records', function($table)
            {
                $table->dropColumn('deliverable_number');
            });
        }
    }
}
