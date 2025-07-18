<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\SalesOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data for clean testing - using database-agnostic approach
        // For PostgreSQL, we'll disable triggers temporarily
        $connection = DB::connection()->getDriverName();
        
        if ($connection === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            Customer::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } else if ($connection === 'pgsql') {
            DB::statement('TRUNCATE TABLE customers CASCADE');
        } else {
            // For other database types, attempt a simple truncate
            // May fail if foreign key constraints exist
            try {
                Customer::truncate();
            } catch (\Exception $e) {
                $this->command->error("Could not truncate customers table: " . $e->getMessage());
            }
        }
        
        $faker = Faker::create();
        
        // Create a diverse set of customers with different attributes
        $customerTypes = ['retail', 'wholesale', 'distributor', 'vip', 'other'];
        $industries = ['agriculture', 'construction', 'education', 'finance', 'food', 
                      'healthcare', 'hospitality', 'manufacturing', 'retail', 'technology',
                      'transportation', 'other'];
        $paymentTerms = ['net_15', 'net_30', 'net_45', 'net_60', 'cod', 'prepaid'];
        $paymentMethods = ['bank_transfer', 'credit_card', 'cash', 'check', 'paypal'];
        $countries = ['ID', 'US', 'CA', 'MX', 'UK', 'AU', 'SG', 'MY', 'JP', 'CN', 'IN'];
        
        // Create 50 customers with various characteristics
        for ($i = 1; $i <= 50; $i++) {
            $isActive = $faker->boolean(80); // 80% chance of being active
            $creditLimit = $faker->randomFloat(2, 0, 100000);
            
            $customerData = [
                // Basic Information
                'code' => 'CUST' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => $faker->company(),
                'contact_person' => $faker->name(),
                'job_title' => $faker->jobTitle(),
                'email' => $faker->unique()->safeEmail(),
                'phone' => $faker->e164PhoneNumber(),
                'is_active' => $isActive,
                
                // Address Information
                'address' => $faker->streetAddress(),
                'city' => $faker->city(),
                'state' => $faker->state(),
                'postal_code' => $faker->postcode(),
                'country' => $faker->randomElement($countries),
                'website' => $faker->url(),
                'location_map' => null, // We'll skip actual file uploads in the seeder
                
                // Financial Information
                'tax_number' => $faker->numerify('##.###.###.#-###.###'),
                'credit_limit' => $creditLimit,
                'payment_terms' => $faker->randomElement($paymentTerms),
                'payment_method' => $faker->randomElement($paymentMethods),
                'bank_name' => $faker->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI', 'CIMB Niaga']),
                'bank_account_number' => $faker->numerify('##########'),
                'notes' => $faker->boolean(30) ? $faker->paragraph() : null, // 30% chance of having notes
                
                // Additional Information
                'customer_type' => $faker->randomElement($customerTypes),
                'industry' => $faker->randomElement($industries),
                'referred_by' => $faker->boolean(20) ? $faker->name() : null, // 20% chance of having a referrer
                'general_notes' => $faker->boolean(40) ? $faker->paragraph() : null, // 40% chance of having general notes
                
                'created_at' => $faker->dateTimeBetween('-2 years', 'now'),
                'updated_at' => $faker->dateTimeBetween('-1 year', 'now'),
            ];

            // Create a few interesting edge cases for testing the UI
            if ($i <= 5) {
                switch($i) {
                    case 1: // Very long values
                        $customerData['name'] = 'PT. ' . $faker->words(8, true) . ' ' . $faker->company();
                        $customerData['address'] = $faker->paragraphs(1, true);
                        break;
                    case 2: // Customer over credit limit 
                        $customerData['credit_limit'] = 1000;
                        // We'll create SalesOrder records for this customer with a sum > credit_limit
                        break;
                    case 3: // International customer with different format address
                        $customerData['country'] = 'US';
                        $customerData['address'] = $faker->streetAddress() . "\n" . $faker->secondaryAddress();
                        $customerData['tax_number'] = $faker->numerify('##-#######');
                        break;
                    case 4: // Inactive VIP customer
                        $customerData['is_active'] = false;
                        $customerData['customer_type'] = 'vip';
                        $customerData['credit_limit'] = 50000;
                        break;
                    case 5: // Customer with all possible data filled
                        $customerData['website'] = 'https://www.example-' . $faker->domainWord() . '.com';
                        $customerData['general_notes'] = $faker->paragraphs(3, true);
                        $customerData['notes'] = $faker->paragraphs(2, true);
                        break;
                }
            }
            
            // Create the customer
            $customer = Customer::create($customerData);
        }
        
        $this->command->info('Successfully seeded 50 customers with varied attributes!');
    }
}
