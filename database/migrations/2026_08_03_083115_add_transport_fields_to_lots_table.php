<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->string('destination')->nullable()->after('chambre_id');
            $table->boolean('transport_avec_go')->default(true)->after('destination');
            $table->decimal('frais_transport', 15, 2)->nullable()->after('transport_avec_go');
            $table->decimal('frais_congelation', 15, 2)->nullable()->after('frais_transport');
            $table->decimal('frais_total', 15, 2)->nullable()->after('frais_congelation');
        });
    }

    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->dropColumn(['destination', 'transport_avec_go', 'frais_transport', 'frais_congelation', 'frais_total']);
        });
    }
};
