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
        if (! Schema::hasTable('categories')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'naam')) {
                $table->string('naam')->unique()->after('id');
            }
            if (!Schema::hasColumn('categories', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('naam');
            }
            if (!Schema::hasColumn('categories', 'omschrijving')) {
                $table->text('omschrijving')->nullable()->after('slug');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'omschrijving')) {
                $table->dropColumn('omschrijving');
            }
            if (Schema::hasColumn('categories', 'slug')) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('categories', 'naam')) {
                $table->dropUnique(['naam']);
                $table->dropColumn('naam');
            }
        });
    }
};
