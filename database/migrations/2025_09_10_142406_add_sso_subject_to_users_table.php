<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSsoSubjectToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add sso_subject column for SSO user identification
            $table->string('sso_subject')->nullable()->unique();
            
            // Add index on sso_subject for efficient user lookups
            $table->index('sso_subject');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the index first, then the column
            $table->dropIndex(['sso_subject']);
            $table->dropColumn('sso_subject');
        });
    }
}
