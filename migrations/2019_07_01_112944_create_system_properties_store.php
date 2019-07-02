<?php

use FastDog\Core\Properties\BasePropertiesStorage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemPropertiesStore extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_properties_store', function (Blueprint $table) {
            $table->bigIncrements('id');


            $table->unsignedInteger(BasePropertiesStorage::PROPERTY_ID);
            $table->unsignedInteger(BasePropertiesStorage::MODEL_ID);
            $table->unsignedInteger(BasePropertiesStorage::ITEM_ID);
            $table->string(BasePropertiesStorage::VALUE, 255);
            $table->unsignedInteger('value_id')->nullable();

            $table->index([BasePropertiesStorage::PROPERTY_ID,
                BasePropertiesStorage::MODEL_ID,
                BasePropertiesStorage::ITEM_ID], 'IDX_system_properties_store');

            $table->index([BasePropertiesStorage::MODEL_ID,
                BasePropertiesStorage::ITEM_ID], 'IDX_system_properties_store_model_id');
        });

        Schema::table('system_properties_store', function ($table) {
            $table->foreign(BasePropertiesStorage::PROPERTY_ID, 'FK_system_properties_store_property_id')
                ->references('id')
                ->on('system_properties');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('system_properties_store')) {
            Schema::table('system_properties_store', function ($table) {
                $table->dropForeign('FK_system_properties_store_pro');
            });
        }
        Schema::dropIfExists('system_properties_store');
    }
}
