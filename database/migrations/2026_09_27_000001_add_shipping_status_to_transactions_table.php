<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The shipping lifecycle, in order. Delivering is only reachable from a
     * paid transaction, so cancelled orders stay on the default value.
     *
     * @var list<string>
     */
    private const STATUSES = ['idle', 'packaging', 'in_transit', 'delivered'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('transactions', 'shipping_status')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('shipping_status', self::STATUSES)
                ->default('idle')
                ->after('is_complete');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('transactions', 'shipping_status')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('shipping_status');
        });

        // Dropping the column leaves the enum type behind on pgsql.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP TYPE IF EXISTS transactions_shipping_status');
        }
    }
};
