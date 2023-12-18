<?php

use Illuminate\Database\Migrations\Migration;

class AddDefaultServerDeletionKeyToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insertOrIgnore(
            [
                'key' => 'pteranodon::renewal:deletion',
                'value' => 'true',
            ],
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')
            ->where('key', 'pteranodon::renewal:deletion')
            ->delete();
    }
}
