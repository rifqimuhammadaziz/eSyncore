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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->decimal('quantity_available', 15, 2)->default(0);
            $table->decimal('quantity_reserved', 15, 2)->default(0);
            $table->decimal('minimum_stock', 15, 2)->default(0);
            $table->decimal('reorder_point', 15, 2)->default(0);
            $table->string('bin_location')->nullable();
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Add unique constraint to ensure one product per warehouse
            $table->unique(['product_id', 'warehouse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
