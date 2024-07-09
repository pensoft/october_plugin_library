<?php namespace Pensoft\Library\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdatePensoftLibraryRecords2 extends Migration
{
    public function up()
    {
        if(Schema::hasTable('pensoft_library_records'))
        {
            Schema::table('pensoft_library_records', function($table)
            {
                if (!Schema::hasColumn('pensoft_library_records', 'keywords')) 
                {
                    $table->text('keywords')->nullable();
                }

                if (!Schema::hasColumn('pensoft_library_records', 'description')) 
                {
                    $table->text('description')->nullable();
                }
            });
        }
    }
    
    public function down()
    {
        if(Schema::hasTable('pensoft_library_records'))
        {
            Schema::table('pensoft_library_records', function($table)
            {
                if (Schema::hasColumn('pensoft_library_records', 'keywords')) 
                {
                    $table->dropColumn('keywords');
                }

                if (Schema::hasColumn('pensoft_library_records', 'description')) 
                {
                    $table->dropColumn('description');
                }
            });
        }
    }

}
