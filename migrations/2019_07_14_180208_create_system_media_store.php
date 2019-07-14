<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use FastDog\Core\Media\BaseMedia;

class CreateSystemMediaStore extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('system_media_store')) {
            Schema::create('system_media_store', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer(BaseMedia::ITEM_ID)->nullable()->comment('Идентификатор объекта');
                $table->integer(BaseMedia::MEDIA_ID)->nullable()->comment('Идентификатор файла');
                $table->integer(BaseMedia::MODEL_ID)->nullable()->comment('Идентификатор модели');
                $table->integer(BaseMedia::SORT)->default(100)->nullable()->comment('Индекс сортировки');
                $table->json(BaseMedia::DATA)->nullable()->comment('Дополнительные данные');
                $table->char(BaseMedia::HASH, 32)->nullable()->comment('Хэш файла');

                $table->index([BaseMedia::ITEM_ID, BaseMedia::MODEL_ID, BaseMedia::MEDIA_ID], 'IDX_system_media_store');
                $table->index([BaseMedia::ITEM_ID, BaseMedia::MODEL_ID, BaseMedia::HASH], 'IDX_system_media_store_hash');
            });
            DB::statement("ALTER TABLE `system_media_store` comment 'Привязка файлов к объектам'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_media_store');
    }
}
