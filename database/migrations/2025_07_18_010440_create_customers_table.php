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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('code')->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('job_title')->nullable(); // New field
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Address Information
            $table->text('address')->nullable(); // Changed from string to text
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('website')->nullable(); // New field
            $table->string('location_map')->nullable(); // New field
            
            // Financial Information
            $table->string('tax_number')->nullable();
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->string('payment_terms')->nullable(); // New field
            $table->string('payment_method')->nullable(); // New field
            $table->string('bank_name')->nullable(); // New field
            $table->string('bank_account_number')->nullable(); // New field
            
            // Additional Information
            $table->string('customer_type')->nullable(); // New field
            $table->string('industry')->nullable(); // New field
            $table->string('referred_by')->nullable(); // New field
            $table->text('notes')->nullable(); // Financial notes
            $table->text('general_notes')->nullable(); // New field
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
