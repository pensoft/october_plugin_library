<?php namespace Pensoft\Library\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use October\Rain\Database\Updates\Migration;

class CreateLibraryStatusesTable extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pensoft_library_statuses')) {
            return;
        }

        Schema::create('pensoft_library_statuses', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->integer('sort_order')->default(0);
        });

        DB::table('pensoft_library_statuses')->insert([
            ['id' => 1, 'name' => 'Published',               'sort_order' => 1],
            ['id' => 2, 'name' => 'In Press',                 'sort_order' => 2],
            ['id' => 3, 'name' => 'In Preparation',           'sort_order' => 3],
            ['id' => 4, 'name' => 'Other',                    'sort_order' => 4],
            ['id' => 5, 'name' => 'Not approved by REA/EC',   'sort_order' => 5],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pensoft_library_statuses');
    }
}
