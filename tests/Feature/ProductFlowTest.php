<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Product;

class ProductFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $category;
    protected $unit;

    protected function setUp(): void
    {
        parent::setUp();
        // run migrations
        $this->artisan('migrate');

        // create a user and give permission if permission system exists; otherwise act as user
        $this->user = User::factory()->create();

        // seed minimal category and unit
        $this->category = Category::factory()->create(['name' => 'Default Cat']);
        $this->unit = Unit::factory()->create(['name' => 'pcs', 'symbol' => 'pc']);
    }

    public function test_create_stocked_product_succeeds()
    {
        $payload = [
            'name' => 'Test Stocked',
            'type' => 'STOCKED',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'mrp' => 100.50,
            'locked_price' => 90.00,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/products', $payload);
        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['name' => 'Test Stocked', 'type' => 'STOCKED']);
    }

    public function test_create_non_stocked_with_prices_fails()
    {
        $payload = [
            'name' => 'Test NonStocked',
            'type' => 'NON_STOCKED',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'mrp' => 50.00,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/products', $payload);
        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Validation failed']);
    }

    public function test_update_stocked_to_non_stocked_clears_prices()
    {
        $product = Product::create([
            'name' => 'P1',
            'type' => 'STOCKED',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'mrp' => 200.00,
            'locked_price' => 180.00,
        ]);

        $payload = ['type' => 'NON_STOCKED'];
        $response = $this->actingAs($this->user)->putJson("/api/products/{$product->id}", $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'type' => 'NON_STOCKED',
            'mrp' => null,
            'locked_price' => null,
        ]);
    }

    public function test_partial_update_does_not_clear_nullable_fields()
    {
        $product = Product::create([
            'name' => 'P2',
            'type' => 'STOCKED',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'supplier_id' => null,
            'cabin_number' => 'A1',
            'img' => 'https://example.com/img.png',
            'color' => 'red',
            'barcode' => 'BC-123',
        ]);

        // partial update: only change name
        $payload = ['name' => 'P2-updated'];
        $response = $this->actingAs($this->user)->patchJson("/api/products/{$product->id}", $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'P2-updated',
            'cabin_number' => 'A1',
            'img' => 'https://example.com/img.png',
            'barcode' => 'BC-123',
        ]);
    }
}
