<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('invoice_number')->unique();
            $table->string('name');
            $table->string('phone');
            $table->string('address');
            $table->unsignedBigInteger('total_amount');
            $table->enum(
                'status',
                [
                    'pending',
                    'paid',
                    'cancelled',
                ]
            )->default('pending');
            $table->string('payment_type')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->boolean('is_complete')->default(false);

            $table->timestamps();
        });

        // is_complete must always mirror the terminal statuses.
        // SQLite has no ALTER TABLE ADD CONSTRAINT, so only enforce on pgsql.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_status_complete_check CHECK ((status IN ('paid', 'cancelled')) = is_complete)");
        }

        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

            $table->string('product_name');
            $table->unsignedBigInteger('price');
            $table->unsignedSmallInteger('quantity');
            $table->unsignedBigInteger('subtotal');

            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();

            $table->string('order_id')->unique();
            $table->string('snap_token')->unique();
            $table->string('status')->default('active');
            $table->string('method')->nullable();
            $table->string('midtrans_transaction_id')->nullable()->index();
            $table->unsignedBigInteger('amount');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
        });

        // At most one active payment session per transaction.
        // Supported by both pgsql and sqlite.
        DB::statement("CREATE UNIQUE INDEX payments_one_active_per_transaction ON payments (transaction_id) WHERE status = 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
    }
};
