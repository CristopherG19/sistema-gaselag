<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quejas', function (Blueprint $table) {
            $table->unsignedBigInteger('oc_id')->nullable()->after('remesa_id');
            $table->foreign('oc_id')->references('id')->on('entregas_cargas')->onDelete('set null');
            $table->index('oc_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quejas', function (Blueprint $table) {
            $table->dropForeign(['oc_id']);
            $table->dropIndex(['oc_id']);
            $table->dropColumn('oc_id');
        });
    }
};
