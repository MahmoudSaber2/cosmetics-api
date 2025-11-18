<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Role $superAdminRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->superAdminRole = Role::create(['name' => 'superAdmin']);
        Role::create(['name' => 'supervisor']);

        // Create permissions
        $permissions = [
            'create-users',
            'update-users',
            'update-user-status',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::create(['name' => $permission]);
        }

        // Assign all permissions to superAdmin
        $this->superAdminRole->givePermissionTo($permissions);

        // Create admin user with permissions
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password')
        ]);
        $this->admin->assignRole('superAdmin');
    }

    // StoreUserRequest Tests

    public function test_store_user_requires_name()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => ['supervisor']
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_user_requires_email()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Test User',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => ['supervisor']
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_user_requires_valid_email_format()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Test User',
                'email' => 'invalid-email',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => ['supervisor']
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_user_requires_unique_email()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Test User',
                'email' => 'existing@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => ['supervisor']
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_user_requires_password()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'roles' => ['supervisor']
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_store_user_requires_password_minimum_length()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'short',
                'password_confirmation' => 'short',
                'roles' => ['supervisor']
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_store_user_requires_password_confirmation()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'different123',
                'roles' => ['supervisor']
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_store_user_validates_phone_format()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'phone' => 'invalid-phone!@#',
                'roles' => ['supervisor']
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_store_user_accepts_valid_phone_formats()
    {
        $validPhones = [
            '1234567890',
            '+1234567890',
            '123-456-7890',
            '(123) 456-7890',
            '+1 (123) 456-7890',
        ];

        foreach ($validPhones as $phone) {
            $response = $this->actingAs($this->admin, 'sanctum')
                ->postJson('/api/v1/admin/users', [
                    'name' => 'Test User',
                    'email' => 'test' . rand(1000, 9999) . '@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                    'phone' => $phone,
                    'roles' => ['supervisor']
                ]);

            $response->assertStatus(201);
        }
    }

    public function test_store_user_phone_is_optional()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => ['supervisor']
            ]);

        $response->assertStatus(201);
    }

    public function test_store_user_address_is_optional()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => ['supervisor']
            ]);

        $response->assertStatus(201);
    }

    public function test_store_user_validates_status_values()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'status' => 5,
                'roles' => ['supervisor']
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_store_user_accepts_valid_status_values()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'status' => 1,
                'roles' => ['supervisor']
            ]);

        $response->assertStatus(201);
    }

    public function test_store_user_requires_roles()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['roles']);
    }

    public function test_store_user_validates_roles_exist()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => ['nonexistent-role']
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['roles.0']);
    }

    public function test_store_user_requires_create_users_permission()
    {
        $userWithoutPermission = User::factory()->create();

        $response = $this->actingAs($userWithoutPermission, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => ['supervisor']
            ]);

        $response->assertStatus(403);
    }

    // UpdateUserRequest Tests

    public function test_update_user_name_is_optional()
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/users/{$user->id}", [
                'email' => 'newemail@example.com',
            ]);

        $response->assertStatus(200);
    }

    public function test_update_user_validates_email_format()
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/users/{$user->id}", [
                'email' => 'invalid-email',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_update_user_validates_email_uniqueness_except_current_user()
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user1->assignRole('supervisor');

        $user2 = User::factory()->create(['email' => 'user2@example.com']);
        $user2->assignRole('supervisor');

        // Should fail - email belongs to another user
        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/users/{$user1->id}", [
                'email' => 'user2@example.com',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Should succeed - same email as current user
        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/users/{$user1->id}", [
                'email' => 'user1@example.com',
            ]);

        $response->assertStatus(200);
    }

    public function test_update_user_validates_password_minimum_length()
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/users/{$user->id}", [
                'password' => 'short',
                'password_confirmation' => 'short',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_update_user_requires_password_confirmation()
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/users/{$user->id}", [
                'password' => 'password123',
                'password_confirmation' => 'different123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_update_user_validates_phone_format()
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/users/{$user->id}", [
                'phone' => 'invalid-phone!@#',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_update_user_accepts_valid_phone_formats()
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor');

        $validPhones = [
            '1234567890',
            '+1234567890',
            '123-456-7890',
            '(123) 456-7890',
        ];

        foreach ($validPhones as $phone) {
            $response = $this->actingAs($this->admin, 'sanctum')
                ->putJson("/api/v1/admin/users/{$user->id}", [
                    'phone' => $phone,
                ]);

            $response->assertStatus(200);
        }
    }

    public function test_update_user_validates_roles_exist()
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/users/{$user->id}", [
                'roles' => ['nonexistent-role']
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['roles.0']);
    }

    public function test_update_user_requires_update_users_permission()
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor');

        $userWithoutPermission = User::factory()->create();

        $response = $this->actingAs($userWithoutPermission, 'sanctum')
            ->putJson("/api/v1/admin/users/{$user->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(403);
    }

    // UpdateUserStatusRequest Tests

    public function test_update_status_requires_status_field()
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/v1/admin/users/{$user->id}/status", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_update_status_validates_status_values()
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/v1/admin/users/{$user->id}/status", [
                'status' => 5
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_update_status_accepts_valid_status_values()
    {
        $user = User::factory()->create(['status' => 1]);
        $user->assignRole('supervisor');

        // Test status 0
        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/v1/admin/users/{$user->id}/status", [
                'status' => 0
            ]);

        $response->assertStatus(200);

        // Test status 1
        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/v1/admin/users/{$user->id}/status", [
                'status' => 1
            ]);

        $response->assertStatus(200);
    }

    public function test_update_status_requires_update_user_status_permission()
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor');

        $userWithoutPermission = User::factory()->create();

        $response = $this->actingAs($userWithoutPermission, 'sanctum')
            ->patchJson("/api/v1/admin/users/{$user->id}/status", [
                'status' => 0
            ]);

        $response->assertStatus(403);
    }

    // Phone Number Format Validation Tests

    public function test_phone_rejects_letters()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'phone' => 'abcdefghij',
                'roles' => ['supervisor']
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_phone_rejects_special_characters()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'phone' => '123@456#789',
                'roles' => ['supervisor']
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_phone_accepts_international_format()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'phone' => '+1 (555) 123-4567',
                'roles' => ['supervisor']
            ]);

        $response->assertStatus(201);
    }

    public function test_phone_validates_max_length()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'phone' => '123456789012345678901',
                'roles' => ['supervisor']
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }
}
