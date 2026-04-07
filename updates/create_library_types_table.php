<?php namespace Pensoft\Library\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use October\Rain\Database\Updates\Migration;

class CreateLibraryTypesTable extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pensoft_library_types')) {
            return;
        }

        Schema::create('pensoft_library_types', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->integer('sort_order')->default(0);
        });

        DB::table('pensoft_library_types')->insert([
            ['id' => 1,  'name' => 'Journal Paper',      'sort_order' => 1],
            ['id' => 2,  'name' => 'Proceedings Paper',   'sort_order' => 2],
            ['id' => 3,  'name' => 'Book Chapter',        'sort_order' => 3],
            ['id' => 4,  'name' => 'Book',                'sort_order' => 4],
            ['id' => 5,  'name' => 'Deliverable',         'sort_order' => 5],
            ['id' => 6,  'name' => 'Report',              'sort_order' => 6],
            ['id' => 7,  'name' => 'Video',               'sort_order' => 7],
            ['id' => 8,  'name' => 'Presentation',        'sort_order' => 8],
            ['id' => 9,  'name' => 'Other',               'sort_order' => 9],
            ['id' => 10, 'name' => 'Pledges',             'sort_order' => 10],
            ['id' => 11, 'name' => 'Milestone',           'sort_order' => 11],
            ['id' => 12, 'name' => 'Feature',             'sort_order' => 12],
            ['id' => 13, 'name' => 'Technical brief',     'sort_order' => 13],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pensoft_library_types');
    }
}
