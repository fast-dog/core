<?php

use FastDog\Core\Properties\BaseProperties;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemProperties extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_properties', function (Blueprint $table) {
            $table->increments('id');
            $table->string(BaseProperties::NAME, 50)->nullable();
            $table->string(BaseProperties::ALIAS, 50)->nullable();
            $table->string(BaseProperties::TYPE, 10)->nullable();
            $table->integer(BaseProperties::MODEL)->nullable()->comment('HEX строки название модели переведенный в INT');
            $table->integer(BaseProperties::SORT)->default(100);
            $table->string(BaseProperties::VALUE)->nullable();
            $table->json(BaseProperties::DATA)->comment('Дополнительные параметры');

            $table->index(BaseProperties::MODEL, 'IDX_system_properties_model');

            $table->timestamps();
            $table->softDeletes();

        });

        DB::statement("ALTER TABLE `system_properties` comment 'Справочник дополнительных параметров моделей'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_properties');
    }
}
