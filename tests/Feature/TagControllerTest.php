<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_store_a_tag(): void
    {
        $response = $this->post('/admin/tags', ['name' => 'vwxyz']);

        $response->assertRedirect('/login');
        $this->assertDatabaseMissing('tags', ['name' => 'vwxyz']);
    }

    public function test_authenticated_user_can_store_a_tag(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/tags', ['name' => 'zyxwv']);

        $response->assertRedirect('/admin');
        $this->assertDatabaseHas('tags', ['name' => 'zyxwv']);
    }

    public function test_store_fails_validation_when_name_is_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/tags', ['name' => '']);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_guest_cannot_view_the_edit_page(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->get('/admin/tags/' . $tag->id . '/edit');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_the_edit_page(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->actingAs($user)->get('/admin/tags/' . $tag->id . '/edit');

        $response->assertStatus(200);
        $response->assertViewIs('admin.tags.edit');
        $response->assertSee($tag->name);
    }

    public function test_guest_cannot_update_a_tag(): void
    {
        $tag = Tag::factory()->create(['name' => '質問']);

        $response = $this->put('/admin/tags/' . $tag->id, ['name' => '要望']);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => '質問']);
    }

    public function test_authenticated_user_can_update_a_tag(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['name' => '質問']);

        $response = $this->actingAs($user)->put('/admin/tags/' . $tag->id, ['name' => '要望']);

        $response->assertRedirect('/admin');
        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => '要望']);
    }

    public function test_update_fails_validation_when_name_is_already_used_by_another_tag(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['name' => '質問']);
        $other = Tag::factory()->create(['name' => '要望']);

        $response = $this->actingAs($user)->put('/admin/tags/' . $tag->id, ['name' => '要望']);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_guest_cannot_destroy_a_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->delete('/admin/tags/' . $tag->id);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
    }

    public function test_authenticated_user_can_destroy_a_tag(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->actingAs($user)->delete('/admin/tags/' . $tag->id);

        $response->assertRedirect('/admin');
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}
