<?php namespace Pensoft\Library\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdatePensoftLibraryRecords extends Migration
{
    public function up(): void
    {
        Schema::table('pensoft_library_records', function(Blueprint $table)
        {
            $table->string('pages', 255)->nullable(false)->unsigned(false)->default('0')->change();
        });
    }

    public function down(): void
    {
        // Schema::table('pensoft_library_records', function(Blueprint $table)
        // {
        //     $table->integer('pages')->nullable(false)->unsigned(false)->default(0)->change();
        // });
    }
}