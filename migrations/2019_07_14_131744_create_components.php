<?php

use FastDog\Core\Models\Components;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateComponents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('components')) {
            Schema::create('components', function (Blueprint $table) {
                $table->increments('id');
                $table->string(Components::NAME)->unique()->comment('Название');
                $table->char(Components::SITE_ID, 3)->default('000')->comment('Код сайта');
                $table->json(Components::DATA)->comment('Дополнительные параметры');
                $table->string('view', 50);
                $table->tinyInteger(Components::STATE)->default(Components::STATE_NOT_PUBLISHED)->comment('Состояние');
                $table->timestamps();
                $table->softDeletes();
                $table->index(Components::SITE_ID, 'IDX_components_site_id');
            });
            DB::statement("ALTER TABLE `components` comment 'Реализация контейнеров выводимых в публичной части сайта'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('components');
    }
}
