<?php

use FastDog\Core\Properties\BasePropertiesSelectValues;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemPropertiesSelectValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_properties_select_values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer(BasePropertiesSelectValues::PROPERTY_ID);
            $table->string(BasePropertiesSelectValues::NAME, 50);
            $table->string(BasePropertiesSelectValues::ALIAS, 50);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('system_properties_select_values', function ($table) {
            $table->foreign(BasePropertiesSelectValues::PROPERTY_ID, 'FK_system_properties')
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
        if (Schema::hasTable('system_properties_select_values')) {
            Schema::table('system_properties_select_values', function ($table) {
                $table->dropForeign('FK_system_properties');
            });
        }

        Schema::dropIfExists('system_properties_select_values');
    }
}
