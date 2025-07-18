<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default Indonesian company: CV Sumber Jaya
        Company::create([
            // General Company Information
            'name' => 'CV Sumber Jaya',
            'legal_name' => 'CV Sumber Jaya Makmur Sentosa',
            'tax_id' => '01.234.567.8-901.000',  // Indonesian NPWP format
            'registration_number' => 'AHU-12345.AH.01.01.2020',
            'industry' => 'retail',
            'description' => 'Perusahaan dagang dan distributor berbagai kebutuhan rumah tangga dan industrial yang berlokasi di Jawa Timur, Indonesia.',
            
            // Contact & Address Information
            'email' => 'info@sumberjaya.co.id',
            'phone' => '+62 813-5678-9012',
            'fax' => '+62 31-8765432',
            'website' => 'https://www.sumberjaya.co.id',
            'address' => 'Jalan Raya Darmo No. 123',
            'city' => 'Surabaya',
            'state' => 'Jawa Timur',
            'postal_code' => '60264',
            'country' => 'ID',
            
            // Locale & Currency Settings
            'language' => 'id',
            'timezone' => 'Asia/Jakarta',
            'date_format' => 'd/m/Y',  // Indonesian date format DD/MM/YYYY
            'time_format' => 'H:i',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'currency_position' => 'before',
            'decimal_precision' => 0,  // Indonesian Rupiah typically doesn't use decimals
            'thousand_separator' => '.',  // Indonesian uses dot as thousand separator
            'decimal_separator' => ',',  // Indonesian uses comma as decimal separator
            
            // Business Settings
            'fiscal_year_start' => '01-01',  // January 1st
            'accounting_method' => 'accrual',
            'default_payment_terms' => 'net_30',
            'invoice_due_days' => 30,
            'quote_valid_days' => 14,
            
            // System & Appearance
            'primary_color' => '#d32f2f',  // Red theme
            'secondary_color' => '#ffeb3b',  // Yellow (for Indonesian flag colors: red and white)
            'theme_mode' => 'light',
            'enable_notifications' => true,
            'is_active' => true,
            
            // Company Contact Person
            'contact_person_name' => 'Budi Santoso',
            'contact_person_position' => 'Direktur Utama',
            'contact_person_email' => 'budi@sumberjaya.co.id',
            'contact_person_phone' => '+62 811-2345-6789',
            
            // Social Media
            'social_media' => json_encode([
                'facebook' => 'https://facebook.com/sumberjaya',
                'instagram' => 'https://instagram.com/sumberjaya_id',
                'twitter' => 'https://twitter.com/sumberjaya',
                'linkedin' => 'https://linkedin.com/company/cv-sumber-jaya',
            ]),
            
            // Additional Settings
            'additional_settings' => json_encode([
                'company_slogan' => 'Mitra Terpercaya Untuk Kebutuhan Anda',
                'company_values' => 'Integritas, Inovasi, Pelayanan',
                'year_established' => 2005,
            ]),
        ]);
        
        $this->command->info('Default Indonesian company "CV Sumber Jaya" has been created!');
    }
}
