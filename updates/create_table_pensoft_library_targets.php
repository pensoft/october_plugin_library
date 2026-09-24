<?php namespace Pensoft\Library\Updates;

use Schema;
use DB;
use Carbon\Carbon;
use October\Rain\Database\Updates\Migration;

/**
 * Target audiences for library records (editable in the backend: Library > Target audiences)
 * and the records.target_id belongsTo column.
 */
class CreateTablePensoftLibraryTargets extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pensoft_library_targets')) {
            Schema::create('pensoft_library_targets', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id')->unsigned();
                $table->string('name');
                $table->string('slug')->unique();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });

            // Initial options; they can be renamed, reordered, removed or extended in the backend.
            $now = Carbon::now();
            $options = ['Academics', 'Researchers', 'Policy makers', 'Technical experts'];
            foreach ($options as $i => $name) {
                DB::table('pensoft_library_targets')->insert([
                    'name' => $name,
                    'slug' => str_slug($name),
                    'sort_order' => $i + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if (Schema::hasTable('pensoft_library_records') && !Schema::hasColumn('pensoft_library_records', 'target_id')) {
            Schema::table('pensoft_library_records', function ($table) {
                $table->integer('target_id')->unsigned()->nullable()->index();
                $table->foreign('target_id')
                    ->references('id')->on('pensoft_library_targets')
                    ->onDelete('set null');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pensoft_library_records') && Schema::hasColumn('pensoft_library_records', 'target_id')) {
            Schema::table('pensoft_library_records', function ($table) {
                $table->dropForeign(['target_id']);
                $table->dropColumn('target_id');
            });
        }
        Schema::dropIfExists('pensoft_library_targets');
    }
}
