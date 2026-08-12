<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
{
    Schema::table('lots', function (Blueprint $table) {
        $table->foreignId('tunnel_id')->nullable()->constrained()->onDelete('set null');
        $table->foreignId('chambre_id')->nullable()->constrained('chambre_froides')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('lots', function (Blueprint $table) {
        $table->dropForeign(['tunnel_id']);
        $table->dropColumn('tunnel_id');
        $table->dropForeign(['chambre_id']);
        $table->dropColumn('chambre_id');
    });
}
};
