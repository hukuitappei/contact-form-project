<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_json_list(): void
    {
        $category = Category::factory()->create();
        Contact::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/v1/contacts');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'meta']);
    }

    public function test_index_filters_by_category(): void
    {
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();
        Contact::factory()->create(['category_id' => $categoryA->id]);
        Contact::factory()->create(['category_id' => $categoryB->id]);

        $response = $this->getJson("/api/v1/contacts?category_id={$categoryA->id}");

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_index_returns_422_for_invalid_gender(): void
    {
        $response = $this->getJson('/api/v1/contacts?gender=9');
        $response->assertStatus(422);
    }

    public function test_show_returns_contact_detail(): void
    {
        $contact = Contact::factory()->create();
        $response = $this->getJson("/api/v1/contacts/{$contact->id}");
        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $contact->id);
    }

    public function test_show_returns_404_for_nonexistent_id(): void
    {
        $response = $this->getJson('/api/v1/contacts/99999');
        $response->assertStatus(404);
        $response->assertJsonPath('error', 'お問い合わせが見つかりませんでした。');
    }

    private function validContactPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '01234567890',
            'address' => 'Japan',
            'detail' => 'お問い合わせです',
        ], $overrides);
    }

    public function test_store_creates_contact_and_returns_201(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactPayload(['category_id' => $category->id]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('contacts', ['email' => $data['email']]);
    }

    public function test_store_returns_422_for_invalid_data(): void
    {
        $response = $this->postJson('/api/v1/contacts', []); // 必須項目が全て欠けたデータ
        $response->assertStatus(422);
    }

    public function test_update_updates_contact_and_returns_200(): void
    {
        $category = Category::factory()->create();
        $contact = Contact::factory()->create(['category_id' => $category->id]);
        $data = $this->validContactPayload([
            'category_id' => $category->id,
            'first_name' => '更新後',
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'first_name' => '更新後']);
    }

    public function test_update_returns_404_for_nonexistent_id(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactPayload(['category_id' => $category->id]);

        $response = $this->putJson('/api/v1/contacts/99999', $data);

        $response->assertStatus(404);
    }

    public function test_update_returns_422_for_invalid_data(): void
    {
        $contact = Contact::factory()->create();
        $response = $this->putJson("/api/v1/contacts/{$contact->id}", ['gender' => 9]);
        $response->assertStatus(422);
    }

    public function test_destroy_deletes_contact_and_returns_204(): void
    {
        $contact = Contact::factory()->create();
        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_id(): void
    {
        $response = $this->deleteJson('/api/v1/contacts/99999');
        $response->assertStatus(404);
    }
}
