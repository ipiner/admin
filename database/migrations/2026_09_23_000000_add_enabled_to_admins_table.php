<?php

use Illuminate\Database\Schema\Blueprint;
use Pin\Database\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->schema()->table('admins', function (Blueprint $table) {
            $this->useTable($table);
            $this->unsignedTinyInteger('enabled', '启用|0: 否; 1: 是')
                ->default(1)
                ->after('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->schema()->table('admins', function (Blueprint $table) {
            $table->dropColumn('enabled');
        });
    }
};
