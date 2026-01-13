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
        if (! Schema::hasTable('stocks')) {
            return;
        }

        Schema::table('stocks', function (Blueprint $table) {
            if (!Schema::hasColumn('stocks', 'product_id')) {
                $table->foreignId('product_id')->constrained('products')->after('id')->onDelete('cascade');
            }
            if (!Schema::hasColumn('stocks', 'aantal')) {
                $table->integer('aantal')->default(0)->after('product_id');
            }
            if (!Schema::hasColumn('stocks', 'laatst_aangepast_op')) {
                $table->timestamp('laatst_aangepast_op')->nullable()->after('aantal');
            }
            if (!Schema::hasColumn('stocks', 'laatst_aangepast_door')) {
                $table->foreignId('laatst_aangepast_door')->nullable()->constrained('users')->after('laatst_aangepast_op')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('stocks')) {
            return;
        }

        Schema::table('stocks', function (Blueprint $table) {
            if (Schema::hasColumn('stocks', 'laatst_aangepast_door')) {
                $table->dropForeign(['laatst_aangepast_door']);
                $table->dropColumn('laatst_aangepast_door');
            }
            if (Schema::hasColumn('stocks', 'laatst_aangepast_op')) {
                $table->dropColumn('laatst_aangepast_op');
            }
            if (Schema::hasColumn('stocks', 'aantal')) {
                $table->dropColumn('aantal');
            }
            if (Schema::hasColumn('stocks', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
        });
    }
};
