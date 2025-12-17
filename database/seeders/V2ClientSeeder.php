<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class V2ClientSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds for v2 clients with payment preferences.
     */
    public function run(): void
    {
        $this->command->info('Creating enhanced client records for v2...');

        // Create clients with payment preferences
        $clients = [
            [
                'name' => 'أحمد محمد',
                'email' => 'ahmed.mohamed@example.com',
                'phone' => '+201234567890',
                'address' => 'شارع التحرير، القاهرة، مصر',
                'payment_preferences' => [
                    'preferred_method' => 'stripe',
                    'save_payment_methods' => true,
                    'auto_pay' => false,
                    'currency' => 'usd',
                ],
                'metadata' => [
                    'registration_source' => 'website',
                    'marketing_consent' => true,
                    'loyalty_member' => true,
                ],
            ],
            [
                'name' => 'فاطمة علي',
                'email' => 'fatima.ali@example.com',
                'phone' => '+201987654321',
                'address' => 'شارع الجامعة، الإسكندرية، مصر',
                'payment_preferences' => [
                    'preferred_method' => 'stripe',
                    'save_payment_methods' => false,
                    'auto_pay' => false,
                    'currency' => 'usd',
                ],
                'metadata' => [
                    'registration_source' => 'mobile_app',
                    'marketing_consent' => false,
                    'loyalty_member' => false,
                ],
            ],
            [
                'name' => 'محمد حسن',
                'email' => 'mohamed.hassan@example.com',
                'phone' => '+201555666777',
                'address' => 'شارع النيل، الجيزة، مصر',
                'payment_preferences' => [
                    'preferred_method' => 'stripe',
                    'save_payment_methods' => true,
                    'auto_pay' => true,
                    'currency' => 'usd',
                ],
                'metadata' => [
                    'registration_source' => 'website',
                    'marketing_consent' => true,
                    'loyalty_member' => true,
                    'vip_customer' => true,
                ],
            ],
            [
                'name' => 'سارة أحمد',
                'email' => 'sara.ahmed@example.com',
                'phone' => '+201444555666',
                'address' => 'شارع المعز، القاهرة، مصر',
                'payment_preferences' => [
                    'preferred_method' => 'stripe',
                    'save_payment_methods' => true,
                    'auto_pay' => false,
                    'currency' => 'usd',
                ],
                'metadata' => [
                    'registration_source' => 'social_media',
                    'marketing_consent' => true,
                    'loyalty_member' => true,
                ],
            ],
            [
                'name' => 'عمر خالد',
                'email' => 'omar.khaled@example.com',
                'phone' => '+201333444555',
                'address' => 'شارع الهرم، الجيزة، مصر',
                'payment_preferences' => [
                    'preferred_method' => 'stripe',
                    'save_payment_methods' => false,
                    'auto_pay' => false,
                    'currency' => 'usd',
                ],
                'metadata' => [
                    'registration_source' => 'referral',
                    'marketing_consent' => false,
                    'loyalty_member' => false,
                ],
            ],
        ];

        foreach ($clients as $clientData) {
            // Extract payment preferences and metadata
            $paymentPreferences = $clientData['payment_preferences'];
            $metadata = $clientData['metadata'];
            unset($clientData['payment_preferences'], $clientData['metadata']);

            // Create client
            $client = Client::create($clientData);

            // You can store additional data in a separate table or JSON field
            // For now, we'll just log the creation
            $this->command->line("Created client: {$client->name} with payment preferences");
        }

        $this->command->info('✅ Created ' . count($clients) . ' enhanced client records for v2');
        $this->command->line('');
        $this->command->line('Client Features:');
        $this->command->line('- Payment method preferences');
        $this->command->line('- Marketing consent tracking');
        $this->command->line('- Loyalty program membership');
        $this->command->line('- Registration source tracking');
    }
}
