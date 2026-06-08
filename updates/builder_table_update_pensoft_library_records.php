<?php namespace Pensoft\Library\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdatePensoftLibraryRecords extends Migration
{
    public function up()
    {
        Schema::table('pensoft_library_records', function($table)
        {
            $table->string('pages', 255)->nullable(false)->unsigned(false)->default('0')->change();
        });
    }
    
    public function down()
    {
        // Schema::table('pensoft_library_records', function($table)
        // {
        //     $table->integer('pages')->nullable(false)->unsigned(false)->default(0)->change();
        // });
    }
}
