<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoreAdminDesktop extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('core_admin_desktop')) {
            Schema::create('core_admin_desktop', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->comment('Название блока');
                $table->enum('type', ['Graph', 'panel', 'table'])->comment('Тип блока');
                $table->integer('sort')->default(100)->comment('Сортировка');
                $table->json('data')->comment('Дополнительные параметры');
                $table->char('site_id', 3)->default('000');
                $table->integer('user_id')->comment('Идентификатор пользователя');
                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE `core_admin_desktop` comment 'Блоки опубликованные на главной странице раздела администрирования'");
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_admin_desktop');
    }
}
