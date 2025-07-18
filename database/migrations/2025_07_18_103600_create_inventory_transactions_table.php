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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->string('transaction_type'); // stock_in, stock_out, adjustment_add, adjustment_remove, transfer_in, transfer_out, sale, purchase, return
            $table->string('reference_type')->nullable(); // sales_order, purchase_order, stock_adjustment, transfer, etc.
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of the related record (e.g., sales_order.id)
            $table->decimal('quantity', 15, 2); // Positive for additions, negative for deductions
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('employees');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
