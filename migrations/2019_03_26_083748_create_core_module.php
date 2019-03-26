<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoreModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_modules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('Название модуля');
            $table->json('data')->comment('Дополнительные параметры');
            $table->tinyInteger('state')->default(0)->comment('Состояние');
            $table->string('version')->comment('Версия модуля')->nullable();
            $table->integer('priority')->comment('Приоритет отображения в меню')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('name', 'UK_core_modules_name');
        });
        DB::statement("ALTER TABLE `core_modules` comment 'Установленные модули'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_modules');
    }
}
