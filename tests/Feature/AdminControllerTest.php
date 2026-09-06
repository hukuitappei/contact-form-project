<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_index(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_index_displays_contacts_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        Contact::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
        $response->assertViewHas('contacts');
        $response->assertViewHas('categories');
    }

    public function test_index_filters_by_keyword(): void
    {
        $user = User::factory()->create();
        $match = Contact::factory()->create(['first_name' => '検索太郎']);
        $other = Contact::factory()->create(['first_name' => '別人']);

        $response = $this->actingAs($user)->get('/admin?' . http_build_query(['keyword' => '検索太郎']));

        $response->assertSee($match->first_name);
        $response->assertDontSee($other->first_name);
    }

    public function test_index_filters_by_gender(): void
    {
        $user = User::factory()->create();
        $male = Contact::factory()->create(['gender' => 1]);
        $female = Contact::factory()->create(['gender' => 2]);

        $response = $this->actingAs($user)->get('/admin?' . http_build_query(['gender' => 1]));

        $response->assertSee($male->email);
        $response->assertDontSee($female->email);
    }

    public function test_index_filters_by_category(): void
    {
        $user = User::factory()->create();
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();
        $matched = Contact::factory()->create(['category_id' => $categoryA->id]);
        $other = Contact::factory()->create(['category_id' => $categoryB->id]);

        $response = $this->actingAs($user)->get('/admin?' . http_build_query(['category_id' => $categoryA->id]));

        $response->assertSee($matched->email);
        $response->assertDontSee($other->email);
    }

    public function test_index_filters_by_date(): void
    {
        $user = User::factory()->create();

        $matched = Contact::factory()->create();
        $matched->created_at = '2026-01-01 10:00:00';
        $matched->save();

        $other = Contact::factory()->create();
        $other->created_at = '2026-02-01 10:00:00';
        $other->save();

        $response = $this->actingAs($user)->get('/admin?' . http_build_query(['date' => '2026-01-01']));

        $response->assertSee($matched->email);
        $response->assertDontSee($other->email);
    }

    public function test_index_paginates_results_with_seven_per_page(): void
    {
        $user = User::factory()->create();
        Contact::factory()->count(8)->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertViewHas('contacts', fn ($contacts) => $contacts->count() === 7);
    }

    public function test_guest_is_redirected_to_login_when_accessing_show(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->get('/admin/contacts/' . $contact->id);

        $response->assertRedirect('/login');
    }

    public function test_show_displays_contact_details(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create();

        $response = $this->actingAs($user)->get('/admin/contacts/' . $contact->id);

        $response->assertStatus(200);
        $response->assertViewIs('admin.show');
        $response->assertSee($contact->email);
    }

    public function test_show_returns_404_for_nonexistent_contact(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/contacts/999999');

        $response->assertStatus(404);
    }

    public function test_guest_cannot_destroy_contact(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->delete('/admin/contacts/' . $contact->id);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('contacts', ['id' => $contact->id]);
    }

    public function test_destroy_deletes_contact_and_redirects(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create();

        $response = $this->actingAs($user)->delete('/admin/contacts/' . $contact->id);

        $response->assertRedirect('/admin');
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }
}
