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
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->unsignedBigInteger('source_warehouse_id');
            $table->unsignedBigInteger('destination_warehouse_id');
            $table->date('transfer_date');
            $table->string('status')->default('draft'); // draft, pending, approved, cancelled, completed
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Add foreign key constraints - only if the referenced tables exist
            if (Schema::hasTable('warehouses')) {
                $table->foreign('source_warehouse_id')->references('id')->on('warehouses');
                $table->foreign('destination_warehouse_id')->references('id')->on('warehouses');
            }
            
            if (Schema::hasTable('employees')) {
                $table->foreign('created_by')->references('id')->on('employees');
                $table->foreign('approved_by')->references('id')->on('employees');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
