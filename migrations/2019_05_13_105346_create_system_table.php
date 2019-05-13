<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('system_table')) {
            Schema::create('system_table', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->json('data')->comment('Дополнительные параметры');
                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE `system_table` comment 'Таблицы в разделе администрирования'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_table');
    }
}
