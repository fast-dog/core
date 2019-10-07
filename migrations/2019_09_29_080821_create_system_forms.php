<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSystemForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('system_forms')) {
            Schema::create('system_forms', function(Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->integer('model')->nullable()->comment('HEX строки название модели переведенный в INT');
                $table->integer('user_id')->nullable()->comment('Идентификатор пользователя');
                $table->json('data')->comment('Дополнительные параметры');
                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE `system_forms` comment 'Формы в разделе администрирования'");
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_forms');
    }
}
