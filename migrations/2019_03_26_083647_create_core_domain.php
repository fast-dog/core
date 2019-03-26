<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoreDomain extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('core_domain')) {
            Schema::create('core_domain', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->comment('Название');
                $table->string('url')->comment('HTTP адрес домена в формате http://{name}.{com}');
                $table->char('code', 3)->default('000')->comment('Код сайта');
                $table->json('data')->comment('Дополнительные параметры');
                $table->tinyInteger('state')->default(0)->comment('Состояние');
                $table->char('site_id', 3)->default('000')->comment('Код домена');
                $table->char('lang', 2)->nullable()->default('ru')->comment('Локализация домена');
                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE `core_domain` comment 'Доступные домены'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_domain');
    }


}
