<?php

namespace Tests\Feature\Api;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_blocked(): void
    {
        // Attempt to access a protected route without a token
        $response = $this->postJson('/api/products');

        $response->assertUnauthorized(); // Asserts HTTP 401
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        // Create a user and the permission they will need
        $user = User::factory()->create();
        Permission::create(['name' => 'Create Products', 'slug' => 'products.create']);

        // Authenticate the user
        Sanctum::actingAs($user);

        // Attempt to access the route that requires the permission (which the user doesn't have)
        $response = $this->postJson('/api/products', ['name' => 'New Product']);

        $response->assertForbidden(); // Asserts HTTP 403
    }

    public function test_user_with_permission_can_access_route(): void
    {
        // Create a user and the required permission
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'Create Products', 'slug' => 'products.create']);

        // Attach the permission to the user
        $user->permissions()->attach($permission);

        // Authenticate the user
        Sanctum::actingAs($user);

        // Attempt to access the route
        $response = $this->postJson('/api/products', ['name' => 'New Product']);

        $response->assertCreated(); // Asserts HTTP 201
    }
}