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
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_transfer_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 15, 2);
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Add foreign key constraints - only if the referenced tables exist
            if (Schema::hasTable('stock_transfers')) {
                $table->foreign('stock_transfer_id')
                    ->references('id')
                    ->on('stock_transfers')
                    ->onDelete('cascade');
            }
            
            if (Schema::hasTable('products')) {
                $table->foreign('product_id')
                    ->references('id')
                    ->on('products');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
    }
};
