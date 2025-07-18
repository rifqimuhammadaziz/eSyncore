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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->unsignedBigInteger('supplier_id');
            $table->date('po_date');
            $table->date('expected_delivery_date')->nullable();
            $table->string('status')->default('draft'); // draft, submitted, approved, received, cancelled, etc.
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->string('payment_terms')->nullable();
            $table->string('payment_status')->default('unpaid'); // unpaid, partial, paid
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Add foreign key constraints only if referenced tables exist
            if (Schema::hasTable('suppliers')) {
                $table->foreign('supplier_id')
                    ->references('id')
                    ->on('suppliers');
            }
            
            if (Schema::hasTable('users')) {
                $table->foreign('created_by')
                    ->references('id')
                    ->on('users');
                    
                $table->foreign('approved_by')
                    ->references('id')
                    ->on('users');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
