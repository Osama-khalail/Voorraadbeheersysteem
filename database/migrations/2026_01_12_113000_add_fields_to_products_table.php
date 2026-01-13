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
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'naam')) {
                $table->string('naam')->after('id');
            }
            if (!Schema::hasColumn('products', 'type')) {
                $table->string('type')->nullable()->after('naam');
            }
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->nullable()->unique()->after('type');
            }
            if (!Schema::hasColumn('products', 'omschrijving')) {
                $table->text('omschrijving')->nullable()->after('sku');
            }
            if (!Schema::hasColumn('products', 'minimale_voorraad')) {
                $table->integer('minimale_voorraad')->default(0)->after('omschrijving');
            }
            if (!Schema::hasColumn('products', 'foto_url')) {
                $table->string('foto_url')->nullable()->after('minimale_voorraad');
            }
            if (!Schema::hasColumn('products', 'categorie_id')) {
                $table->unsignedBigInteger('categorie_id')->nullable()->after('foto_url');
                $table->foreign('categorie_id')->references('id')->on('categories')->onDelete('SET NULL');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'categorie_id')) {
                $table->dropForeign(['categorie_id']);
                $table->dropColumn('categorie_id');
            }
            if (Schema::hasColumn('products', 'foto_url')) {
                $table->dropColumn('foto_url');
            }
            if (Schema::hasColumn('products', 'minimale_voorraad')) {
                $table->dropColumn('minimale_voorraad');
            }
            if (Schema::hasColumn('products', 'omschrijving')) {
                $table->dropColumn('omschrijving');
            }
            if (Schema::hasColumn('products', 'sku')) {
                $table->dropUnique(['sku']);
                $table->dropColumn('sku');
            }
            if (Schema::hasColumn('products', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('products', 'naam')) {
                $table->dropColumn('naam');
            }
        });
    }
};
