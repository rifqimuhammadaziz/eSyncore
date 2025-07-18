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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->foreignId('warehouse_id')->constrained();
            $table->date('adjustment_date');
            $table->string('reference_number')->nullable();
            $table->string('reason'); // physical_count, damage, expiry, theft, return, supplier_return, other
            $table->string('status')->default('draft'); // draft, pending, approved, cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('employees');
            $table->foreignId('approved_by')->nullable()->constrained('employees');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
