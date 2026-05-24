<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClientApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user for authentication
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password')
        ]);
    }

    public function test_admin_can_list_clients()
    {
        // Create some test clients
        Client::factory()->count(3)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/clients');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'clients',
                    'pagination'
                ],
                'message'
            ]);
    }

    public function test_admin_can_create_client()
    {
        $clientData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'city' => 'New York'
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/clients', $clientData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data',
                'message'
            ]);

        $this->assertDatabaseHas('clients', [
            'email' => 'john@example.com'
        ]);
    }

    public function test_admin_can_view_client()
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/admin/clients/{$client->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message'
            ]);
    }

    public function test_admin_can_update_client()
    {
        $client = Client::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'email' => $client->email, // Keep same email
            'phone' => '9876543210',
            'address' => 'Updated Address',
            'city' => 'Updated City'
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/admin/clients/{$client->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Updated Name'
        ]);
    }

    public function test_admin_can_delete_client_without_orders()
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/admin/clients/{$client->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('clients', [
            'id' => $client->id
        ]);
    }

    public function test_unauthenticated_user_cannot_access_admin_endpoints()
    {
        $response = $this->getJson('/api/admin/clients');

        $response->assertStatus(401);
    }

    public function test_client_search_functionality()
    {
        Client::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        Client::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/clients?search=John');

        $response->assertStatus(200);

        $clients = $response->json('data.clients');
        $this->assertCount(1, $clients);
        $this->assertEquals('John Doe', $clients[0]['name']);
    }
}
