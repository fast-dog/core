<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemTableFilter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_table_filter', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('table_id');
            $table->string('name');
            $table->json('data')->comment('Дополнительные параметры');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('system_table_filter', function (Blueprint $table) {
            $db = config('database.connections.mysql.database');
            $table->foreign('table_id', 'FK_system_table_filter_table_id')
                ->references('id')
                ->on('system_table');
        });

        DB::statement("ALTER TABLE `system_table_filter` comment 'Таблицы в разделе администрирования'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('system_table_filter')) {
            Schema::table('system_table_filter', function (Blueprint $table) {
                $table->dropForeign('FK_system_table_filter_table_id');
            });
            Schema::dropIfExists('system_table_filter');
        }

    }
}
