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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->string('description')->nullable();
            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_price', 15, 2);
            $table->string('unit')->nullable(); // e.g., pcs, kg, liter
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0); // quantity * unit_price
            $table->decimal('total', 15, 2)->default(0); // subtotal + tax_amount - discount_amount
            $table->decimal('received_quantity', 15, 3)->default(0); // For partial receiving
            $table->string('status')->default('pending'); // pending, partial, received
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
