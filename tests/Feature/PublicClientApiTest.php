<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PublicClientApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_can_register_new_client()
    {
        $clientData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'address' => '123 Main St',
            'city' => 'New York'
        ];

        $response = $this->postJson('/api/public/clients', $clientData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'client' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'address',
                        'city'
                    ],
                    'is_existing'
                ],
                'message'
            ]);

        $data = $response->json('data');
        $this->assertFalse($data['is_existing']);
        $this->assertEquals('John Doe', $data['client']['name']);
        $this->assertEquals('john@example.com', $data['client']['email']);

        $this->assertDatabaseHas('clients', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }

    public function test_can_update_existing_client_information()
    {
        // Create existing client
        $existingClient = Client::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+1111111111',
            'address' => '456 Old St',
            'city' => 'Boston'
        ]);

        $updatedData = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com', // Same email
            'phone' => '+2222222222', // Updated phone
            'address' => '789 New St', // Updated address
            'city' => 'Chicago' // Updated city
        ];

        $response = $this->postJson('/api/public/clients', $updatedData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'client',
                    'is_existing'
                ],
                'message'
            ]);

        $data = $response->json('data');
        $this->assertTrue($data['is_existing']);
        $this->assertEquals('Jane Smith', $data['client']['name']);
        $this->assertEquals('+2222222222', $data['client']['phone']);

        $this->assertDatabaseHas('clients', [
            'id' => $existingClient->id,
            'name' => 'Jane Smith',
            'phone' => '+2222222222',
            'address' => '789 New St',
            'city' => 'Chicago'
        ]);
    }

    public function test_client_registration_requires_name_and_email()
    {
        $response = $this->postJson('/api/public/clients', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_client_registration_validates_email_format()
    {
        $clientData = [
            'name' => 'John Doe',
            'email' => 'invalid-email'
        ];

        $response = $this->postJson('/api/public/clients', $clientData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_can_find_client_by_email()
    {
        $client = Client::factory()->create([
            'name' => 'Test Client',
            'email' => 'test@example.com'
        ]);

        $response = $this->getJson('/api/public/clients/by-email?email=test@example.com');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'client' => [
                        'id',
                        'name',
                        'email'
                    ]
                ],
                'message'
            ]);

        $data = $response->json('data');
        $this->assertEquals($client->id, $data['client']['id']);
        $this->assertEquals('Test Client', $data['client']['name']);
    }

    public function test_cannot_find_nonexistent_client_by_email()
    {
        $response = $this->getJson('/api/public/clients/by-email?email=nonexistent@example.com');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Client not found'
            ]);
    }

    public function test_find_client_by_email_requires_email_parameter()
    {
        $response = $this->getJson('/api/public/clients/by-email');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_find_client_by_email_validates_email_format()
    {
        $response = $this->getJson('/api/public/clients/by-email?email=invalid-email');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_client_registration_handles_long_strings()
    {
        $clientData = [
            'name' => str_repeat('a', 300), // Too long
            'email' => 'test@example.com',
            'address' => str_repeat('b', 600), // Too long
        ];

        $response = $this->postJson('/api/public/clients', $clientData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'address']);
    }

    public function test_client_registration_with_minimal_data()
    {
        $clientData = [
            'name' => 'Minimal Client',
            'email' => 'minimal@example.com'
            // No phone, address, or city
        ];

        $response = $this->postJson('/api/public/clients', $clientData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('clients', [
            'name' => 'Minimal Client',
            'email' => 'minimal@example.com',
            'phone' => null,
            'address' => null,
            'city' => null
        ]);
    }
}
