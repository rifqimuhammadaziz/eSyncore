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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('so_number')->unique();
            $table->foreignId('customer_id')->constrained();
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('status')->default('draft');
            $table->string('payment_status')->default('pending');
            $table->string('shipping_method')->nullable();
            $table->string('shipping_status')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_tracking')->nullable();
            $table->date('delivery_date')->nullable();
            
            // Amounts
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            
            // Approval info
            $table->foreignId('created_by')->nullable()->constrained('employees');
            $table->foreignId('approved_by')->nullable()->constrained('employees');
            $table->timestamp('approved_at')->nullable();
            
            // Notes
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
