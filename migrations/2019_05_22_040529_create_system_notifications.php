<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('system_notifications')) {
            Schema::create('system_notifications', function (Blueprint $table) {
                $table->increments('id');
                $table->integer(self::USER_ID)->comment('Идентификатор пользователя');
                $table->json(self::DATA)->comment('Дополнительные параметры');
                $table->tinyInteger(self::READ)->default(0)->comment('Прочитано или нет');
                $table->mediumInteger(self::TYPE)->default(0);

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE `system_notifications` comment 'Сообщения от cms'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_notifications');
    }
}
