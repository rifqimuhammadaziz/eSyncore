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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            
            // General Company Information
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('industry')->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            
            // Contact & Address Information
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('google_maps_url')->nullable();
            
            // Locale & Currency Settings
            $table->string('language')->default('en'); // Default language code
            $table->string('timezone')->default('UTC');
            $table->string('date_format')->default('Y-m-d');
            $table->string('time_format')->default('H:i');
            $table->string('currency_code')->default('USD');
            $table->string('currency_symbol')->default('$');
            $table->string('currency_position')->default('before'); // 'before' or 'after' the amount
            $table->integer('decimal_precision')->default(2);
            $table->string('thousand_separator')->default(',');
            $table->string('decimal_separator')->default('.');
            
            // Business Settings
            $table->string('fiscal_year_start')->default('01-01'); // MM-DD format
            $table->string('accounting_method')->default('accrual'); // 'accrual' or 'cash'
            $table->string('default_payment_terms')->nullable();
            $table->integer('invoice_due_days')->default(30);
            $table->integer('quote_valid_days')->default(30);
            
            // System & Appearance
            $table->string('primary_color')->default('#4338ca'); // Default to indigo-700
            $table->string('secondary_color')->default('#f59e0b'); // Default to amber-500
            $table->string('theme_mode')->default('light'); // 'light', 'dark', or 'system'
            $table->boolean('enable_notifications')->default(true);
            $table->boolean('is_active')->default(true);
            
            // Company Contact Person
            $table->string('contact_person_name')->nullable();
            $table->string('contact_person_email')->nullable();
            $table->string('contact_person_phone')->nullable();
            $table->string('contact_person_position')->nullable();
            
            // Social Media
            $table->json('social_media')->nullable(); // Store as JSON: {"facebook":"url", "twitter":"url", etc.}
            
            // Additional Settings (stored as JSON for flexibility)
            $table->json('additional_settings')->nullable();
            
            $table->timestamps();
            $table->softDeletes(); // Allow soft deletion of company records
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
