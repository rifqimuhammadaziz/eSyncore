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
        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->decimal('current_quantity', 15, 2)->default(0);
            $table->decimal('new_quantity', 15, 2)->default(0);
            $table->decimal('quantity', 15, 2)->comment('The adjustment amount (can be positive or negative)');
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_items');
    }
};
