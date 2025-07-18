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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('selling_price', 15, 2);
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('unit')->nullable(); // e.g., pcs, kg, liter
            $table->string('barcode')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->string('image_path')->nullable();
            $table->json('attributes')->nullable(); // For additional product attributes
            $table->timestamps();
            $table->softDeletes();
            
            // Add foreign key constraint only if categories table exists
            if (Schema::hasTable('categories')) {
                $table->foreign('category_id')
                    ->references('id')
                    ->on('categories')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
