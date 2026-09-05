<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Contact;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    private function validContactData(array $overrides = []): array
    {
        return array_merge([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => '1',
            'email' => 'taro@example.com',
            'tel' => '01234567890',
            'address' => 'Japan',
            'detail' => 'お問い合わせです',
        ], $overrides);
    }

    public function test_displays_the_contact_form(): void
    {
        $category = Category::factory()->create(['content' => 'テストカテゴリ']);
        $tag = Tag::factory()->create(['name' => 'テストタグ']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('contact.index');
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee($category->content);
        $response->assertSee($tag->name);
    }

    public function test_confirm_displays_submitted_values(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData(['category_id' => $category->id]);

        $response = $this->post('/contacts/confirm', $data);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertSee('太郎');
        $response->assertSee($category->content);
    }

    public function test_confirm_displays_selected_tags(): void
    {
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();
        $data = $this->validContactData([
            'category_id' => $category->id,
            'tag_ids' => $tags->pluck('id')->toArray(),
        ]);

        $response = $this->post('/contacts/confirm', $data);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        foreach ($tags as $tag) {
            $response->assertSee($tag->name);
        }
    }

    public function test_confirm_fails_validation_when_required_fields_are_missing(): void
    {
        $response = $this->post('/contacts/confirm', []);

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);
    }

    public function test_store_contact_with_valid_input_and_tags_creates_record_and_attachments(): void
    {
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(3)->create();
        $payload = $this->validContactData([
            'category_id' => $category->id,
            'tag_ids' => $tags->pluck('id')->toArray(),
        ]);

        $response = $this->post('/contacts', $payload);

        $response->assertRedirect('/thanks');
        $this->assertDatabaseHas('contacts', [
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'email' => $payload['email'],
        ]);

        $contact = Contact::where('email', $payload['email'])->first();
        foreach ($tags as $tag) {
            $this->assertDatabaseHas('contact_tag', [
                'contact_id' => $contact->id,
                'tag_id' => $tag->id,
            ]);
        }
    }

    public function test_store_contact_without_tags_creates_record_without_attachments(): void
    {
        $category = Category::factory()->create();
        $payload = $this->validContactData([
            'email' => 'hanako@example.com',
            'category_id' => $category->id,
        ]);

        $response = $this->post('/contacts', $payload);

        $response->assertRedirect('/thanks');
        $this->assertDatabaseHas('contacts', [
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'email' => $payload['email'],
        ]);

        $contact = Contact::where('email', $payload['email'])->first();
        $this->assertDatabaseMissing('contact_tag', [
            'contact_id' => $contact->id,
        ]);
        $this->assertDatabaseHas('contacts', ['building' => null]);
    }

    public function test_store_fails_validation_when_required_fields_are_missing(): void
    {
        $response = $this->post('/contacts', []);

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_store_fails_validation_when_category_and_tag_ids_do_not_exist(): void
    {
        $data = $this->validContactData([
            'category_id' => 9999,
            'tag_ids' => [9999],
        ]);

        $response = $this->post('/contacts', $data);

        $response->assertSessionHasErrors(['category_id', 'tag_ids.0']);
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_store_fails_validation_when_gender_is_out_of_range(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData(['gender' => 4, 'category_id' => $category->id]);
        $response = $this->post('/contacts', $data);
        $response->assertSessionHasErrors(['gender']);
    }

    public function test_store_fails_validation_when_tel_is_too_short(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData(['category_id' => $category->id, 'tel' => '012345678',]);
        $response = $this->post('/contacts', $data);
        $response->assertSessionHasErrors(['tel']);
    }

    public function test_store_fails_validation_when_tel_is_too_long(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData(['category_id' => $category->id, 'tel' => '012345678910',]);
        $response = $this->post('/contacts', $data);
        $response->assertSessionHasErrors(['tel']);

    }

    public function test_store_fails_validation_when_tel_contains_non_numeric_characters(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData(['category_id' => $category->id, 'tel' => '0123-4567-8910',]);
        $response = $this->post('/contacts', $data);
        $response->assertSessionHasErrors(['tel']);
    }

    public function test_store_fails_validation_when_email_format_is_invalid(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData([
            'category_id' => $category->id,
            'email' => 'not-an-email',
        ]);

        $response = $this->post('/contacts', $data);

        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_store_succeeds_when_detail_is_exactly_max_length(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData([
            'category_id' => $category->id,
            'detail' => str_repeat('あ', 120),
        ]);

        $response = $this->post('/contacts', $data);

        $response->assertRedirect('/thanks');
        $this->assertDatabaseCount('contacts', 1);
    }

    public function test_store_fails_validation_when_detail_exceeds_max_length(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData([
            'category_id' => $category->id,
            'detail' => str_repeat('あ', 121),
        ]);

        $response = $this->post('/contacts', $data);

        $response->assertSessionHasErrors(['detail']);
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_store_saves_building_when_provided(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData([
            'category_id' => $category->id,
            'building' => '〇〇マンション101',
        ]);

        $response = $this->post('/contacts', $data);

        $response->assertRedirect('/thanks');
        $this->assertDatabaseHas('contacts', [
            'email' => $data['email'],
            'building' => '〇〇マンション101',
        ]);
    }

    public function test_displays_the_contact_form_with_no_categories_or_tags(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('contact.index');
        $response->assertViewHas('categories', fn($categories) => $categories->isEmpty());
        $response->assertViewHas('tags', fn($tags) => $tags->isEmpty());
    }

    public function test_store_fails_validation_when_first_name_exceeds_max_length(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData([
            'category_id' => $category->id,
            'first_name' => str_repeat('太', 256),
        ]);

        $response = $this->post('/contacts', $data);

        $response->assertSessionHasErrors(['first_name']);
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_store_fails_validation_when_last_name_exceeds_max_length(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData([
            'category_id' => $category->id,
            'last_name' => str_repeat('山', 256),
        ]);

        $response = $this->post('/contacts', $data);

        $response->assertSessionHasErrors(['last_name']);
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_store_fails_validation_when_address_exceeds_max_length(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData([
            'category_id' => $category->id,
            'address' => str_repeat('a', 256),
        ]);

        $response = $this->post('/contacts', $data);

        $response->assertSessionHasErrors(['address']);
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_store_fails_validation_when_email_exceeds_max_length(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData([
            'category_id' => $category->id,
            'email' => str_repeat('a', 250) . '@ex.com',
        ]);

        $response = $this->post('/contacts', $data);

        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_store_fails_validation_when_gender_is_not_an_integer(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData([
            'category_id' => $category->id,
            'gender' => 'abc',
        ]);

        $response = $this->post('/contacts', $data);

        $response->assertSessionHasErrors(['gender']);
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_store_fails_validation_when_category_id_is_not_an_integer(): void
    {
        $data = $this->validContactData([
            'category_id' => 'abc',
        ]);

        $response = $this->post('/contacts', $data);

        $response->assertSessionHasErrors(['category_id']);
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_store_fails_validation_when_building_exceeds_max_length(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData([
            'category_id' => $category->id,
            'building' => str_repeat('a', 250) . 'マンション101',
        ]);

        $response = $this->post('/contacts', $data);

        $response->assertSessionHasErrors(['building']);
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_store_fails_validation_when_tag_ids_contain_non_integer_value(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData([
            'tag_ids' => ['abc'],
            'category_id' => $category->id,
        ]);

        $response = $this->post('/contacts', $data);

        $response->assertSessionHasErrors(['tag_ids.0']);
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_displays_the_contact_form_lists_multiple_categories_and_tags(): void
    {
        $categories = Category::factory()->count(2)->create();
        $tags = Tag::factory()->count(2)->create();

        $response = $this->get('/');

        $response->assertStatus(200);
        foreach ($categories as $category) {
            $response->assertSee($category->content);
        }
        foreach ($tags as $tag) {
            $response->assertSee($tag->name);
        }
    }

    public function test_thanks_view(): void
    {
        $response = $this->get('/thanks');

        $response->assertStatus(200);
        $response->assertViewIs('contact.thanks');
    }
}
