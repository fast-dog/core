<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoreErrorLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('core_error_log')) {
            Schema::create('core_error_log', function (Blueprint $table) {
                $table->increments('id');
                $table->json('data')->comment('Дополнительные параметры');
                $table->char('site_id', 3)->default('000');
                $table->integer('user_id')->comment('Идентификатор пользователя');
                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE `core_error_log` comment 'Ошибки зафиксированные скриптом'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_error_log');
    }
}
